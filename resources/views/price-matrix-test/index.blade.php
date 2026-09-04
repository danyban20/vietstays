<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Matrix Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-title {
            color: white;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .page-title p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .test-panel {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        button {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
            flex: 1;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .result-box {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            display: none;
        }
        
        .result-box.show {
            display: block;
        }
        
        .result-box h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .result-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 0.95rem;
        }
        
        .result-row:last-child {
            border-bottom: none;
        }
        
        .result-label {
            color: #666;
            font-weight: 500;
        }
        
        .result-value {
            color: #333;
            font-weight: 600;
        }
        
        .result-value.price {
            color: #667eea;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c00;
            padding: 12px;
            border-radius: 6px;
            margin-top: 10px;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #667eea;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .data-table tr:hover {
            background: #fafafa;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
        }
        
        .stat-card h4 {
            opacity: 0.9;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        
        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-title">
            <h1>🎯 Price Matrix Test Suite</h1>
            <p>Test price calculations for apartments by building and type</p>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Available Types</h4>
                <div class="value">{{ count($typeKeys) }}</div>
            </div>
            <div class="stat-card">
                <h4>Districts Configured</h4>
                <div class="value">{{ count($districtIndices) }}</div>
            </div>
            <div class="stat-card">
                <h4>Buildings Available</h4>
                <div class="value">{{ count($buildings) }}</div>
            </div>
            <div class="stat-card">
                <h4>Base Price Range</h4>
                <div class="value">{{ number_format(min($basePrices) / 1000000, 1, '.', '') }}M - {{ number_format(max($basePrices) / 1000000, 1, '.', '') }}M</div>
            </div>
        </div>
        
        <!-- Test Panel -->
        <div class="test-panel">
            <h2 style="margin-bottom: 20px; color: #333;">Calculate Price</h2>
            
            <form id="priceForm">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="building">🏢 Select Building</label>
                        <select id="building" name="building_id" required>
                            <option value="">-- Choose a building --</option>
                            @foreach ($buildings as $building)
                                <option value="{{ $building->id }}">
                                    {{ $building->name }}
                                    @if ($building->district)
                                        ({{ $building->district->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="typeKey">🛏️ Select Apartment Type</label>
                        <select id="typeKey" name="type_key" required>
                            <option value="">-- Choose a type --</option>
                            @foreach ($typeKeys as $key)
                                <option value="{{ $key }}">{{ $key }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn-primary">Calculate Price</button>
                    <button type="reset" class="btn-secondary">Clear</button>
                </div>
            </form>
            
            <div class="error-message" id="errorMessage"></div>
            
            <!-- Result -->
            <div class="result-box" id="resultBox">
                <h3>💰 Price Calculation Result</h3>
                <div class="result-row">
                    <span class="result-label">Building</span>
                    <span class="result-value" id="resultBuilding">-</span>
                </div>
                <div class="result-row">
                    <span class="result-label">District</span>
                    <span class="result-value" id="resultDistrict">-</span>
                </div>
                <div class="result-row">
                    <span class="result-label">Apartment Type</span>
                    <span class="result-value" id="resultType">-</span>
                </div>
                <div class="result-row">
                    <span class="result-label">Base Price</span>
                    <span class="result-value" id="resultBasePrice">-</span>
                </div>
                <div class="result-row">
                    <span class="result-label">District Index</span>
                    <span class="result-value" id="resultDistrictIndex">-</span>
                </div>
                <div class="result-row">
                    <span class="result-label">Building Factor/Override</span>
                    <span class="result-value" id="resultFactor">-</span>
                </div>
                <div class="result-row" style="border-top: 2px solid #667eea; margin-top: 15px; padding-top: 15px;">
                    <span class="result-label">💵 Calculated Price</span>
                    <span class="result-value price" id="resultPrice">-</span>
                </div>
            </div>
        </div>
        
        <!-- Base Prices Table -->
        <div class="test-panel">
            <h2 style="margin-bottom: 20px; color: #333;">📋 Base Prices (PM_BASE)</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Apartment Type</th>
                        <th>Base Price (VND/night)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($basePrices as $type => $price)
                        <tr>
                            <td><strong>{{ $type }}</strong></td>
                            <td>{{ number_format($price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- District Indices Table -->
        <div class="test-panel">
            <h2 style="margin-bottom: 20px; color: #333;">📍 District Indices (PM_DISTRICT_INDEX)</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>District Code</th>
                        <th>Price Index Multiplier</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($districtIndices as $code => $index)
                        <tr>
                            <td><strong>{{ $code }}</strong></td>
                            <td>{{ number_format($index, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('priceForm');
        const resultBox = document.getElementById('resultBox');
        const errorBox = document.getElementById('errorMessage');
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const buildingId = document.getElementById('building').value;
            const typeKey = document.getElementById('typeKey').value;
            
            if (!buildingId || !typeKey) {
                showError('Please select both building and type');
                return;
            }
            
            try {
                const response = await fetch('{{ route("price-matrix-calculate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        building_id: buildingId,
                        type_key: typeKey,
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    showError(data.error || 'Error calculating price');
                    return;
                }
                
                showResult(data);
                hideError();
            } catch (error) {
                showError('Network error: ' + error.message);
            }
        });
        
        function showResult(data) {
            document.getElementById('resultBuilding').textContent = data.building_name;
            document.getElementById('resultDistrict').textContent = `${data.district} (${data.district_code})`;
            document.getElementById('resultType').textContent = data.type_key;
            document.getElementById('resultBasePrice').textContent = number_format(data.base_price);
            document.getElementById('resultDistrictIndex').textContent = data.district_index.toFixed(2);
            
            let factorText = '-';
            if (data.price_override) {
                factorText = `Price Override: ${number_format(data.price_override)} VND`;
            } else if (data.factor_override) {
                factorText = `Factor Override: ${data.factor_override.toFixed(2)}`;
            } else {
                factorText = 'Default (from building hash)';
            }
            document.getElementById('resultFactor').textContent = factorText;
            
            document.getElementById('resultPrice').textContent = data.calculated_price + ' ₫';
            
            resultBox.classList.add('show');
        }
        
        function showError(message) {
            errorBox.textContent = '❌ ' + message;
            errorBox.classList.add('show');
        }
        
        function hideError() {
            errorBox.classList.remove('show');
        }
        
        function number_format(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    </script>
</body>
</html>
