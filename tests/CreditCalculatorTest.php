<?php
use PHPUnit\Framework\TestCase;
use App\Services\CreditCalculator;
use App\Services\AmortizationSchedule;

class CreditCalculatorTest extends TestCase
{
    public function testMonthlyPaymentZeroInterest()
    {
        $this->assertEquals(100, CreditCalculator::monthlyPayment(1200, 0, 12));
    }

    public function testMonthlyPaymentStandard()
    {
        $payment = CreditCalculator::monthlyPayment(100000, 7.5, 60);
        $this->assertEqualsWithDelta(2003.79, $payment, 0.01);
    }

    public function testLinearSchedule()
    {
        $input = [
            'amount' => 50000,
            'months' => 12,
            'interest_type' => 'fixed',
            'rate' => 10,
            'method' => 'linear',
            'advance_percent' => 0,
            'comision_acordare_percent' => 0,
            'start_date' => '2026-06-01'
        ];
        $result = CreditCalculator::generateSchedule($input);
        $this->assertCount(12, $result['schedule']);
        $this->assertEqualsWithDelta(4166.67, $result['schedule'][0]['principal'], 0.01);
    }

    public function testDAE()
    {

        $input = [
            'amount' => 50000,
            'months' => 12,
            'interest_type' => 'fixed',
            'rate' => 10,
            'method' => 'annuity',
            'advance_percent' => 0,
            'comision_acordare_percent' => 0,
            'start_date' => '2026-06-01'
        ];
        $result = CreditCalculator::generateSchedule($input);
        $dae = $result['dae'];
        $this->assertGreaterThan(10, $dae);
        $this->assertLessThan(11, $dae);
    }

    public function testPrincipalSumEqualsAmount()
    {
        $input = [
            'amount' => 50000,
            'months' => 12,
            'interest_type' => 'fixed',
            'rate' => 10,
            'method' => 'annuity',
            'advance_percent' => 10, 
            'comision_acordare_percent' => 0,
            'start_date' => '2026-06-01'
        ];
        $result = CreditCalculator::generateSchedule($input);
        $sumPrincipal = array_sum(array_column($result['schedule'], 'principal'));
        $this->assertEqualsWithDelta(45000, $sumPrincipal, 0.1);
    }
}