<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Logger;
use App\Models\ReferenceRate;

class CreditCalculator
{

    public static function monthlyPayment(float $principal, float $annualRate, int $months): float
    {
        if ($annualRate == 0) return round($principal / $months, 2);
        $monthlyRate = $annualRate / 12 / 100;
        $factor = pow(1 + $monthlyRate, $months);
        return round($principal * $monthlyRate * $factor / ($factor - 1), 2);
    }

    public static function generateSchedule(array $input): array
    {

        $logData = $input;
        unset($logData['csrf_token']);
        Logger::logCalculation($logData);

        $principal = (float)$input['amount'];
        $avansPercent = (float)($input['advance_percent'] ?? 0);
        if ($avansPercent > 0) {
            $principal -= $principal * $avansPercent / 100;
        }

        $months = (int)$input['months'];
        $startDate = new \DateTime($input['start_date'] ?? 'now');
        $interestType = $input['interest_type'] ?? 'fixed';
        $method = $input['method'] ?? 'annuity';
        $gracePeriod = 0;
        $graceType = $input['grace_type'] ?? 'principal'; // 'principal' sau 'total'
        if (!empty($input['grace']) && !empty($input['grace_months'])) {
            $gracePeriod = min((int)$input['grace_months'], 6);
        }

        $comisionAcordarePercent = (float)($input['comision_acordare_percent'] ?? 0);
        $comisionAcordare = round($principal * $comisionAcordarePercent / 100, 2);

        $annualRates = []; 

        if ($interestType === 'fixed') {
            $rate = (float)$input['rate'];
            $annualRates = array_fill(0, $months, $rate);
        } elseif ($interestType === 'variable') {
            $margin = (float)($input['margin'] ?? 2.5);
            $baseRate = (float)($input['base_rate'] ?? null);
            if (!$baseRate) {
                $latest = ReferenceRate::latest();
                $baseRate = $latest ? (float)$latest['base_rate'] : 3.6;
            }
            $annualRates = array_fill(0, $months, $baseRate + $margin);
        } elseif ($interestType === 'mixed') {
            $fixedMonths = (int)($input['fixed_months'] ?? 60);
            $fixedRate = (float)$input['rate'];
            $margin = (float)($input['margin'] ?? 2.5);
            $baseRate = (float)($input['base_rate'] ?? null);
            if (!$baseRate) {
                $latest = ReferenceRate::latest();
                $baseRate = $latest ? (float)$latest['base_rate'] : 3.6;
            }
            for ($i = 0; $i < $months; $i++) {
                if ($i < $fixedMonths) {
                    $annualRates[] = $fixedRate;
                } else {
                    $annualRates[] = $baseRate + $margin;
                }
            }
        }

        $schedule = AmortizationSchedule::generateFlexible(
            principal: $principal,
            annualRates: $annualRates,
            totalMonths: $months,
            graceMonths: $gracePeriod,
            graceType: $graceType,
            method: $method,
            startDate: $startDate->format('Y-m-d')
        );

        $totals = AmortizationSchedule::getTotals($schedule);

        $dae = self::calculateDAE($principal, $schedule, $comisionAcordare);

        return [
            'schedule' => $schedule,
            'total_payments' => $totals['total_payments'],
            'total_interest' => $totals['total_interest'],
            'total_principal' => $totals['total_principal'],
            'comision_acordare' => $comisionAcordare,
            'dae' => round($dae, 2),
            'monthly_payment_first' => count($schedule) > 0 ? $schedule[0]['total_payment'] : 0,
        ];
    }

    private static function calculateDAE(float $netPrincipal, array $schedule, float $comision): float
    {
        $cashflows = [];
        $cashflows[] = -($netPrincipal - $comision);
        foreach ($schedule as $row) {
            $cashflows[] = $row['total_payment'];
        }
        $guess = 0.1 / 12; 
        $irr = self::irr($cashflows, $guess);
        $dae = (pow(1 + $irr, 12) - 1) * 100;
        return $dae;
    }

    private static function irr(array $values, float $guess = 0.1): float
    {
        $x = $guess;
        for ($i = 0; $i < 1000; $i++) {
            $npv = 0;
            $dnpv = 0;
            for ($t = 0; $t < count($values); $t++) {
                $npv += $values[$t] / pow(1 + $x, $t);
                $dnpv -= $t * $values[$t] / pow(1 + $x, $t + 1);
            }
            if (abs($npv) < 1e-6) break;
            $x = $x - $npv / $dnpv;
        }
        return $x;
    }

    public static function earlyRepayment(array $input): array
    {
        $originalSchedule = $input['schedule'] ?? [];
        $atMonth = (int)$input['month'];
        $extraAmount = (float)$input['extra_amount'];
        $option = $input['option'] ?? 'reduce_period';
        $annualRate = (float)$input['annual_rate']; // trebuie transmis din frontend

        $newSchedule = AmortizationSchedule::applyEarlyRepayment(
            $originalSchedule,
            $atMonth,
            $extraAmount,
            $option,
            $annualRate
        );
        $oldTotals = AmortizationSchedule::getTotals($originalSchedule);
        $newTotals = AmortizationSchedule::getTotals($newSchedule);
        $savings = round($oldTotals['total_interest'] - $newTotals['total_interest'], 2);

        return [
            'schedule' => $newSchedule,
            'savings' => $savings,
            'old_total_interest' => $oldTotals['total_interest'],
            'new_total_interest' => $newTotals['total_interest'],
        ];
    }
}