<?php include __DIR__ . '/layout.php'; ?>
<div class="container mt-4">
    <h1 class="mb-4">Simulator Credite</h1>
    <form id="calc-form" class="row g-3">
        <?= \App\Core\Csrf::field() ?>
        <div class="col-md-4">
            <label class="form-label">Tip credit</label>
            <select class="form-select" name="type" id="creditType">
                <option value="ipotecar">Ipotecar / Imobiliar</option>
                <option value="nevoi_personale">Nevoi personale</option>
                <option value="auto">Auto</option>
                <option value="linie_credit">Linie de credit</option>
                <option value="refinantare">Refinantare</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Suma creditului</label>
            <input type="range" class="form-range" min="1000" max="10000000" step="1000" id="amountSlider" value="50000">
            <input type="number" class="form-control" id="amount" name="amount" value="50000">
        </div>
        <div class="col-md-4">
            <label class="form-label">Perioada (luni)</label>
            <input type="range" class="form-range" min="2" max="360" id="monthsSlider" value="60">
            <input type="number" class="form-control" id="months" name="months" value="60">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tip dobanda</label>
            <select class="form-select" name="interest_type" id="interestType">
                <option value="fixed">Fixă</option>
                <option value="variable">Variabilă</option>
                <option value="mixed">Mixtă</option>
            </select>
        </div>
        <div class="col-md-4" id="fixedRateDiv">
            <label class="form-label">Dobândă (%)</label>
            <input type="number" class="form-control" name="rate" id="rate" value="7.5" step="0.01">
        </div>
        <div class="col-md-4" id="variableDiv" style="display:none;">
            <label class="form-label">Marjă peste BNM (%)</label>
            <input type="number" class="form-control" name="margin" id="margin" value="2.5" step="0.01">
            <label class="form-label mt-2">Rata de bază BNM (override)</label>
            <input type="number" class="form-control" name="base_rate" id="baseRate" placeholder="Opțional">
        </div>
        <div class="col-md-4" id="mixedDiv" style="display:none;">
            <label class="form-label">Perioadă fixă (luni)</label>
            <input type="number" class="form-control" name="fixed_months" id="fixedMonths" value="60" min="1">
        </div>
        <div class="col-md-4">
            <label class="form-label">Avans (%)</label>
            <input type="number" class="form-control" name="advance_percent" value="0" step="1">
        </div>
        <div class="col-md-4">
            <label class="form-label">Metodă rambursare</label>
            <select class="form-select" name="method">
                <option value="annuity">Anuități constante</option>
                <option value="linear">Rate descrescătoare</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Venit lunar (opțional)</label>
            <input type="number" class="form-control" name="monthly_income" placeholder="Pentru grad de îndatorare">
        </div>
        <div class="col-md-4">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="grace" id="grace">
                <label class="form-check-label" for="grace">Perioada de gratie</label>
            </div>
            <div id="graceOptions" style="display:none;" class="mt-2">
                <label class="form-label">Luni gratie</label>
                <input type="number" class="form-control" name="grace_months" min="1" max="6" value="3">
                <label class="form-label mt-2">Tip gratie</label>
                <select class="form-select" name="grace_type">
                    <option value="principal">Doar dobândă</option>
                    <option value="total">Totală (capitalizare)</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Comision acordare (%)</label>
            <input type="number" class="form-control" name="comision_acordare_percent" value="0" step="0.01">
        </div>
        <div class="col-12">
            <button type="button" id="calculateBtn" class="btn btn-primary">Calculeaza</button>
        </div>
    </form>

    <div id="results" style="display:none;" class="mt-5">
        </div>

    <div id="earlyRepaymentSection" style="display:none;" class="mt-4 card p-4">
        <h4>Rambursare anticipată</h4>
        <div class="row g-3">
            <div class="col-md-4">
                <label>Luna rambursării</label>
                <input type="number" id="earlyMonth" class="form-control" min="1">
            </div>
            <div class="col-md-4">
                <label>Suma suplimentară</label>
                <input type="number" id="earlyAmount" class="form-control" step="0.01">
            </div>
            <div class="col-md-4">
                <label>Opțiune</label>
                <select id="earlyOption" class="form-select">
                    <option value="reduce_period">Reducere perioadă</option>
                    <option value="reduce_installment">Reducere rată</option>
                </select>
            </div>
            <div class="col-12">
                <button id="earlyRepayBtn" class="btn btn-warning">Aplică</button>
            </div>
        </div>
        <div id="earlyResult" class="mt-3"></div>
    </div>
</div>
<script src="/js/app.js"></script>
</body>
</html>
<?php include __DIR__ . '/footer.php'; ?>