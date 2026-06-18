<?php
declare(strict_types=1);
namespace App\Services;

use DateTime;
use InvalidArgumentException;

class AmortizationSchedule
{
    public static function generateFlexible(
        float $principal,
        array $annualRates,
        int $totalMonths,
        int $graceMonths = 0,
        string $graceType = 'principal',
        string $method = 'annuity',
        string $startDate = 'now'
    ): array {
        if ($principal <= 0 || count($annualRates) !== $totalMonths) {
            throw new InvalidArgumentException('Parametri invalizi.');
        }

        $schedule = [];
        $remaining = $principal;
        $date = new DateTime($startDate);

        $monthlyPayment = null;
        if ($method === 'annuity' && $totalMonths > 0) {
            $monthlyPayment = self::calcAnnuity($remaining, $annualRates[0], $totalMonths - $graceMonths);
        }

        $monthlyPrincipal = $method === 'linear' ? round($principal / ($totalMonths - $graceMonths), 2) : 0;

        for ($i = 0; $i < $totalMonths; $i++) {
            $annualRate = $annualRates[$i];
            $monthlyRate = $annualRate / 12 / 100;
            $interest = round($remaining * $monthlyRate, 2);

            if ($i < $graceMonths) {
                if ($graceType === 'total') {
                    $principalPaid = 0;
                    $totalPayment = 0;
                    $remaining += $interest;
                } else {
                    $principalPaid = 0;
                    $totalPayment = $interest;
                }
            } else {
                if ($method === 'annuity') {
                    $remainingMonths = $totalMonths - $i;
                    if ($remainingMonths > 0) {
                        $monthlyPayment = self::calcAnnuity($remaining, $annualRate, $remainingMonths);
                    }
                    $principalPaid = round($monthlyPayment - $interest, 2);
                    $totalPayment = $monthlyPayment;
                } else {
                    $principalPaid = min($monthlyPrincipal, $remaining);
                    $totalPayment = $principalPaid + $interest;
                }
            }

            if ($i === $totalMonths - 1) {
                $principalPaid = $remaining;
                $totalPayment = $principalPaid + $interest;
            }

            $remaining = round($remaining - $principalPaid, 2);
            if ($remaining < 0) $remaining = 0;

            $schedule[] = [
                'month'             => $i + 1,
                'due_date'          => $date->format('Y-m-d'),
                'total_payment'     => round($totalPayment, 2),
                'principal'         => round($principalPaid, 2),
                'interest'          => round($interest, 2),
                'remaining_balance' => $remaining
            ];

            $date->modify('+1 month');
        }

        return $schedule;
    }

    public static function applyEarlyRepayment(
        array $originalSchedule,
        int $atMonth,
        float $extraAmount,
        string $option,
        float $annualRate
    ): array {
        $index = $atMonth - 1;
        if (!isset($originalSchedule[$index])) {
            throw new InvalidArgumentException('Luna specificată nu există.');
        }

        $newSchedule = array_slice($originalSchedule, 0, $index + 1);
        $current = $originalSchedule[$index];
        $newRemaining = $current['remaining_balance'] - $extraAmount;
        if ($newRemaining < 0) $newRemaining = 0;
        $newSchedule[$index]['remaining_balance'] = round($newRemaining, 2);

        if ($newRemaining == 0) {
            return $newSchedule;
        }

        $remainingMonths = count($originalSchedule) - $atMonth;
        $startDate = new DateTime($originalSchedule[$index]['due_date']);
        $startDate->modify('+1 month');

        if ($option === 'reduce_period') {
            $monthlyPayment = $current['total_payment'];
            $monthlyRate = $annualRate / 12 / 100;
            if ($monthlyRate > 0 && $monthlyPayment > $newRemaining * $monthlyRate) {
                $newMonths = ceil(log($monthlyPayment / ($monthlyPayment - $newRemaining * $monthlyRate)) / log(1 + $monthlyRate));
            } else {
                $newMonths = ceil($newRemaining / $monthlyPayment);
            }
            $subSchedule = self::generateFlexible(
                $newRemaining,
                array_fill(0, (int)$newMonths, $annualRate),
                (int)$newMonths,
                0,
                'principal',
                'annuity',
                $startDate->format('Y-m-d')
            );
            $offset = $atMonth + 1;
            foreach ($subSchedule as $key => $item) {
                $item['month'] = $offset + $key;
                $newSchedule[] = $item;
            }
        } else {
            $subSchedule = self::generateFlexible(
                $newRemaining,
                array_fill(0, $remainingMonths, $annualRate),
                $remainingMonths,
                0,
                'principal',
                'annuity',
                $startDate->format('Y-m-d')
            );
            $offset = $atMonth + 1;
            foreach ($subSchedule as $key => $item) {
                $item['month'] = $offset + $key;
                $newSchedule[] = $item;
            }
        }

        return $newSchedule;
    }

    private static function calcAnnuity(float $principal, float $annualRate, int $months): float
    {
        if ($annualRate == 0) return round($principal / $months, 2);
        $monthlyRate = $annualRate / 12 / 100;
        $factor = pow(1 + $monthlyRate, $months);
        return round($principal * $monthlyRate * $factor / ($factor - 1), 2);
    }

    public static function getTotals(array $schedule): array
    {
        $totalInterest = 0;
        $totalPrincipal = 0;
        foreach ($schedule as $row) {
            $totalInterest += $row['interest'];
            $totalPrincipal += $row['principal'];
        }
        return [
            'total_principal' => round($totalPrincipal, 2),
            'total_interest'  => round($totalInterest, 2),
            'total_payments'  => round($totalPrincipal + $totalInterest, 2),
        ];
    }
}