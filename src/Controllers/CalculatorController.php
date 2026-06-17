<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Core\Csrf;
use App\Services\CreditCalculator;
use App\Models\ReferenceRate;

class CalculatorController
{
    public function calculate(Request $request): Response
    {
        if (!Csrf::validate($request->input('csrf_token', ''))) {
            return new Response(403, ['error' => 'Token CSRF invalid.']);
        }

        $data = $request->all();
        $v = new Validator();
        $v->required('amount', $data['amount'] ?? null)
          ->numeric('amount', $data['amount'] ?? null)
          ->min('amount', $data['amount'] ?? 0, 1000)
          ->max('amount', $data['amount'] ?? 0, 100000000);
        $v->required('months', $data['months'] ?? null)
          ->numeric('months', $data['months'] ?? null)
          ->min('months', $data['months'] ?? 0, 2)
          ->max('months', $data['months'] ?? 0, 360);
        $v->required('interest_type', $data['interest_type'] ?? null)
          ->inArray('interest_type', $data['interest_type'] ?? '', ['fixed', 'variable', 'mixed']);
        if (($data['interest_type'] ?? '') === 'fixed') {
            $v->required('rate', $data['rate'] ?? null)
              ->numeric('rate', $data['rate'] ?? null)
              ->min('rate', $data['rate'] ?? 0, 0)
              ->max('rate', $data['rate'] ?? 0, 50);
        }
        if (($data['interest_type'] ?? '') === 'variable') {
            $v->required('margin', $data['margin'] ?? null)
              ->numeric('margin', $data['margin'] ?? null);
        }
        if (($data['type'] ?? '') === 'ipotecar') {
            $advance = (float)($data['advance_percent'] ?? 0);
            if ($advance < 10) {
                $v->getErrors()['advance_percent'] = 'Avansul minim pentru credit ipotecar este 10%.';
            }
        }
        if (!empty($data['monthly_income'])) {
            $income = (float)$data['monthly_income'];
            $tempPrincipal = (float)$data['amount'];
            $tempRate = (float)($data['rate'] ?? 7.5);
            $tempMonths = (int)$data['months'];
            $estimatedPayment = CreditCalculator::monthlyPayment($tempPrincipal, $tempRate, $tempMonths);
            if ($estimatedPayment > $income * 0.4) {
                $v->getErrors()['monthly_income'] = 'Rata lunară depășește 40% din venitul declarat.';
            }
        }

        if ($v->hasErrors()) {
            return new Response(422, ['errors' => $v->getErrors()]);
        }

        try {
            $result = CreditCalculator::generateSchedule($data);
            return new Response(200, ['result' => $result]);
        } catch (\Throwable $e) {
            return new Response(500, ['error' => $e->getMessage()]);
        }
    }

    public function exportCSV(Request $request): Response
    {
        $data = $request->all();
        $schedule = $data['schedule'] ?? [];
        $csv = "Luna,Data scadenta,Rata totala,Principal,Dobanda,Sold ramas\n";
        foreach ($schedule as $row) {
            $csv .= implode(',', [
                $row['month'], $row['due_date'], $row['total_payment'],
                $row['principal'], $row['interest'], $row['remaining_balance']
            ]) . "\n";
        }
        return (new Response(200, $csv))
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="amortizare.csv"');
    }

    public function latestRate(): Response
    {
        $rate = ReferenceRate::latest();
        return new Response(200, ['rate' => $rate]);
    }

    public function earlyRepayment(Request $request): Response
    {
        if (!Csrf::validate($request->input('csrf_token', ''))) {
            return new Response(403, ['error' => 'Token CSRF invalid.']);
        }
        $data = $request->all();
        try {
            $result = CreditCalculator::earlyRepayment($data);
            return new Response(200, ['result' => $result]);
        } catch (\Throwable $e) {
            return new Response(500, ['error' => $e->getMessage()]);
        }
    }
}