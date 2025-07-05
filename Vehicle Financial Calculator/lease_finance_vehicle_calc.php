<?php

add_shortcode('finance_calculator', 'financial_calculator');

function financial_calculator() {
	if ( ! is_product() ) { return ''; }

	global $product;

	$product_id = $product->get_id();
    $visibility = get_field('calc_visibility', $product_id);

    if (empty($visibility)) { return; }

	$price = $product->get_price();
	$interest = get_field('financing_interest_rate', 'option');
	$transfer_costs = get_field('transfer_costs', 'option');
	$lease_rate = get_field('leasing_rate_insurance', 'option');
	$driver_protection = get_field('driver_protection_fee', 'option');
	$gap_protection = get_field('gap_protection_fee', 'option');
	
	ob_start(); ?>
    <style>
        .finance_calculator * { font-family: 'Manrope' !important; font-weight: 500 !important; }
		.finance_calculator { padding: 20px; box-shadow: 0 0 10px 0 rgba(0,0,0,0.20); }
		.calc_wrapper label { display: block; margin-bottom: 10px; }
		.finance_calculator input, select, textarea { height: 50px; width: 100%; padding: 8px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 4px; }
		.finance_calculator textarea { min-height: 130px; }
		.form-toggle { display: flex; gap: 10px; margin-bottom: 20px; }		
		.toggle-btn { flex: 1; padding: 15px; border: 1px solid #EF7D00; font-size: 16px; background: white; color: #EF7D00; cursor: pointer; transition: all 0.3s; }
		.toggle-btn.active { background: #EF7D00; color: white; }
		.calculation {background: #fff8ee; padding: 15px; border-radius: 6px;margin: 20px 0; }
		.calculation h4 { margin-bottom: 10px;}
		.calculation ul { list-style: none; padding: 0; margin: 0;}
		.calculation li label { margin-bottom: 5px; display: flex; justify-content: space-between; color: #000000 }
		.button { background: #EF7D00; color: white; border: none; font-size: 16px !important; padding: 12px 20px; cursor: pointer; border-radius: 4px; transition: background 0.3s; margin: 0 !important }
		.button:hover { background: #d96c00; }
		.hidden { display: none;}
		.finance_wrapper label , .leasing_wrapper label { display: flex; align-items: center; font-size: 16px; cursor: pointer; color: #333333; }
		.finance_wrapper input[type="checkbox"], .leasing_wrapper input[type="checkbox"] { margin: 0; margin-right: 12px; width: 18px; height: 18px; }
		.finance_wrapper span, .leasing_wrapper span { margin-left: auto; font-weight: 600; color: #007bff; font-size: 15px; }	
		.finance_calculator .calculation input { text-align: end !important; all : unset; }
		.loader { opacity: 0; width: 35px; height: 35px;display: inline-block;border-radius: 50%;border: 5px solid;border-color: #edebeb;border-right-color: #ef7d00;animation: spinner-d3wgkg 1s infinite linear; vertical-align: middle;margin-left: 0.75rem; }
		@keyframes spinner-d3wgkg { to { transform: rotate(1turn); } }
		@media(max-width: 576px){
			.finance_calculator .calculation input { width: 130px; }
		}    
    </style>    

	<div id="finance_calculator" class="finance_calculator">
		<div class="form-toggle">
			<button type="button" class="toggle-btn active" data-target="leasing">Leasing</button>
			<button type="button" class="toggle-btn" data-target="financing">Finanzierung</button>
		</div>

		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="calc-form" id="leasing-form">
			<input type="hidden" name="leasing_form" value="1">
			<input type="hidden" id="l_vehiclePrice" name="l_vehiclePrice" value="<?php echo esc_attr($price); ?>" readonly />
			<div class="calc_wrapper">
				<label>Laufzeit (Monate):
					<select id="l_Term" name="l_Term">
						<option value="24">24</option>
						<option value="36">36</option>
						<option value="48">48</option>
						<option value="60">60</option>
					</select>
				</label>
				<label>Anzahlung (€): <input type="number" id="l_Deposit" name="l_Deposit" value="0" min="0" step="1000"/></label>
				<label>Jährliche Fahrleistung:
					<select id="l_Mileage" name="l_Mileage">
						<option value="5000">5.000 km</option>
						<option value="10000">10.000 km</option>
						<option value="15000">15.000 km</option>
						<option value="20000">20.000 km</option>
						<option value="25000">25.000 km</option>
						<option value="30000">30.000 km</option>
					</select>
				</label>
			</div>
			<div class="leasing_wrapper">
				<label><input type="checkbox" id="l_leaseInsurance" name="l_leaseInsurance" value="<?php echo esc_attr($lease_rate); ?>" /> Lease Interest Insurance</label>
				<label><input type="checkbox" id="l_GapProtection" name="l_GapProtection" value="<?php echo esc_attr($gap_protection); ?>" /> GAP Protection</label>
			</div>
			<div class="calculation">
				<h4>Example Calculation:</h4>
				<ul>
					<li><label>Leasing Type: <input type="text" readonly class="l_type" name="l_type" value="Mileage leasing" /></label></li>
					<li><label>Customer Type: <input type="text" readonly class="l_customer_type" name="l_customer_type" value="Private customer" /></label></li>
					<li><label>Purchase Price: <input type="text" readonly class="l_vehicle_price" name="l_vehicle_price" value="<?php echo esc_html($price . "€"); ?>" /></label></li>
					<li><label>Transfer Costs: <input type="text" readonly class="l_transfer_costs" name="l_transfer_costs" value="<?php echo esc_html($transfer_costs); ?>" /></label></li>
					<li><label>Duration: <input type="text" readonly class="leasing_terms" name="l_duration" /></label></li>
					<li><label>Monthly Installments: <input type="text" readonly class="l_monthly_installments" name="l_monthly_installments" /></label></li>
					<li><label>Total Mileage: <input type="text" readonly class="l_total_mileage" name="l_total_mileage" /></label></li>
					<li><label>Total Interest: <input type="text" readonly class="l_total_interest" name="l_total_interest" /></label></li>
					<li><label>Fixed Interest Rate (p.a.): <input type="text" readonly class="l_interest_fixed" name="l_interest_fixed" value="<?php echo esc_html($interest . "%"); ?>" /></label></li>
					<li><label>Effective Annual Interest Rate: <input type="text" readonly class="l_interest_effective" name="l_interest_effective" value="<?php echo esc_html($interest . "%"); ?>" /></label></li>
					<li><label>Total Installment Amount: <input type="text" readonly class="l_installments_total" name="l_installments_total" /></label></li>
					<li><label>Total Amount incl. Fees & Special Payments: <input type="text" readonly class="l_total_amount" name="l_total_amount" /></label></li>
				</ul>
			</div>
			<div class="calc_wrapper">
				<h4>Kontaktdaten</h4>
				<p><input type="text" name="l_name" placeholder="Name" required></p>
				<p><input type="email" name="l_email" placeholder="E-Mail" required></p>
				<p><input type="tel" name="l_phone" placeholder="Telefonnummer"></p>
				<p><textarea name="l_message" placeholder="Anmerkungen / Sonderwünsche"></textarea></p>
				<p><button type="submit" class="button">Anfrage senden</button><span class="loader"></span></p>
				<div class="form-message"></div>
			</div>
		</form>

		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="calc-form hidden" id="financing-form">
			<input type="hidden" name="finance_form" value="1">
			<input type="hidden" id="f_vehiclePrice" name="f_vehiclePrice" value="<?php echo esc_attr($price); ?>" readonly />
			<div class="calc_wrapper">
				<label>Gewünschte Laufzeit:
					<select id="f_Term" name="f_Term">
						<option value="24">24</option>
						<option value="36">36</option>
						<option value="48">48</option>
						<option value="60">60</option>
					</select>
				</label>
				<label>Gewünschte Anzahlung: <input type="number" id="f_Deposit" name="f_Deposit" value="0" min="0" step="1000" required /></label>
				<label>Jährliche Fahrleistung:
					<select id="f_Mileage" name="f_Mileage" required>
						<option value="5000">5.000 km</option>
						<option value="10000">10.000 km</option>
						<option value="15000">15.000 km</option>
						<option value="20000">20.000 km</option>
						<option value="25000">25.000 km</option>
						<option value="30000">30.000 km</option>
					</select>
				</label>
			</div>
			<div class="finance_wrapper">
				<label><input type="checkbox" id="f_RateInsurance" name="f_RateInsurance" value="<?php echo esc_attr($driver_protection); ?>" /> Driver Protection Plus </label>
				<label><input type="checkbox" id="f_gapProtection" name="f_gapProtection" value="<?php echo esc_attr($gap_protection); ?>" /> GAP Protection</label>
			</div>
			<div class="calculation">
				<h4> Beispielrechnung: </h4>
				<ul>
					<li><label>Type of financing: <input type="text" readonly name="f_type" class="f_type" value="3-way financing" /></label></li>
					<li><label>Customer Type: <input type="text" readonly name="f_customer_type" class="f_customer_type" value="Private customer" /></label></li>
					<li><label>Vehicle price financing: <input readonly name="f_vehicle_price" type="text" class="f_vehicle_price" value ="<?php echo esc_html($price . "€"); ?>" /></label></li>
					<li><label>Down Payment: <input type="text" readonly name="f_deposit"  class="f_deposit" value="<?php echo esc_html("0€"); ?>" /></label></li>
					<li><label>Transfer Costs: <input type="text" readonly name="f_transfer_costs" class="f_transfer_costs" value="<?php echo esc_html($transfer_costs); ?>" /></label></li>
					<li><label>Duration: <input type="text" readonly name="f_duration" class="f_duration" /></label></li>
					<li><label>Monthly Installments: <input type="text" readonly name="f_monthly_installments" class="f_monthly_installments" /></label></li>
					<li><label>Total Mileage: <input type="text" readonly  name="f_total_mileage" class="f_total_mileage" /></label></li>
					<li><label>Total Interest: <input type="text" readonly name="f_total_interest" class="f_total_interest" /></label></li>
					<li><label>Final installment: <input type="text" readonly name="f_final_installment" class="f_final_installment" value="-" /></label></li>
					<li><label>Fixed Interest Rate (p.a. <input type="text" readonly name="f_interest_fixed" class="f_interest_fixed" value="<?php echo esc_html($interest . "%"); ?>" /></label></li>
					<li><label>Effective Annual Interest Rate: <input type="text" readonly name="f_interest_effective" class="f_interest_effective" value="<?php echo esc_html($interest . "%"); ?>" /></label></li>
					<li><label>Total Installment Amount: <input type="text" readonly  name="f_installments_total" class="f_installments_total" /></label></li>
					<li><label>Total Amount incl. Fees & Special Payments: <input type="text" readonly name="f_total_amount" class="f_total_amount" /></label></li>
				</ul>
			</div>
			<div class="calc_wrapper">
				<h4>Kontaktdaten</h4>
				<p><input type="text" name="f_name" placeholder="Name" required></p>
				<p><input type="email" name="f_email" placeholder="E-Mail" required></p>
				<p><input type="tel" name="f_phone" placeholder="Telefonnummer"></p>
				<p><textarea name="f_message" placeholder="Anmerkungen / Sonderwünsche"></textarea></p>
				<p><button type="submit" class="button">Anfrage senden</button> <span class="loader"></span> </p>
				<div class="form-message"></div>
			</div>
		</form>
	</div>

	<script defer>
		jQuery(document).ready(function ($) {
			const interest = parseFloat('<?php echo esc_js( $interest / 100 ); ?>') || 0;
			const transfer_cost = parseFloat('<?php echo esc_js($transfer_costs); ?>') || 0;
			const lease_rate = parseFloat('<?php echo esc_js($lease_rate); ?>') || 0;
			const driver_protection = parseFloat('<?php echo esc_js($driver_protection); ?>') || 0;
			const gap_protection = parseFloat('<?php echo esc_js($gap_protection); ?>') || 0;
			
			$('#l_leaseInsurance').on('change', function () { $(this).val(this.checked ? lease_rate : 0 ); });
			$('#l_GapProtection').on('change', function () { $(this).val(this.checked ? gap_protection : 0 ); });
			
			// Leasing Form 
			const $vehiclePrice = $('#l_vehiclePrice');
			const $leasingTerm = $('#l_Term');
			const $deposit = $('#l_Deposit');
			const $mileage = $('#l_Mileage');
			
			function formatEuro(value) {
				return Number(parseFloat(value).toFixed(2)).toLocaleString('de-DE', { 
					style: 'currency', currency: 'EUR', minimumFractionDigits: 2, maximumFractionDigits: 2
				});
			}

			function updateLeasing() {
				const price = parseFloat($vehiclePrice.val()) || 0;
				const term = parseInt($leasingTerm.val()) || 1;
				let downPayment = parseFloat($deposit.val()) || 0;
				const mileagePerYear = parseInt($mileage.val()) || 0;
				const totalMileage = mileagePerYear * (term / 12);
				
				// Lease Rate & Gap Protection
				const LeaseRateInsurance = $('#l_leaseInsurance').is(':checked') ? lease_rate : 0;
				const gapProtectionCost = $('#l_GapProtection').is(':checked') ? gap_protection : 0;
				
				// Deposit validation
				const maxDepositPercent = 0.30;
				let maxDeposit = Math.min(price * maxDepositPercent, price);

				if (downPayment > maxDeposit) {
					downPayment = maxDeposit;
					$deposit.val(downPayment.toFixed(2));
				}
				
				// Depreciation logic
				const baseDepreciation = 0.20; // 20% per year
				const mileageDepreciation = getMileageDepreciation(mileagePerYear); // per year
				const effectiveDepreciation = baseDepreciation + mileageDepreciation;

				const years = term / 12;
				const residualValue = price * Math.pow(1 - effectiveDepreciation, years);

				const netLoan = price - residualValue - downPayment;
				const monthlyInterestRate = interest / 12;
				const monthlyRate = parseFloat(((netLoan * monthlyInterestRate) / (1 - Math.pow(1 + monthlyInterestRate, -term))).toFixed(2));
				const totalInstallments = monthlyRate * term;
				const totalAmount = totalInstallments + downPayment + transfer_cost + LeaseRateInsurance + gapProtectionCost;
				const totalInterest = totalInstallments - netLoan;
				
				function getMileageDepreciation(mileage) {
					if (mileage <= 10000) return 0.00;
					if (mileage <= 15000) return 0.0025;   // +0.25%
					if (mileage <= 20000) return 0.0035;   // +0.35%
					if (mileage <= 25000) return 0.00575;  // +0.575%
					if (mileage <= 30000) return 0.0075;   // +0.75%
					return 0.0075; // beyond 30,000km
				}
				
				$('.leasing_terms').val(`${term} Monate`);
				$('.l_total_mileage').val(`${totalMileage.toLocaleString()} km`);
				$('.l_monthly_installments').val(formatEuro(monthlyRate));
				$('.l_installments_total').val(formatEuro(totalInstallments));
				$('.l_transfer_costs').val(formatEuro(transfer_cost));
				$('.l_total_amount').val(formatEuro(totalAmount));
				$('.l_total_interest').val(formatEuro(totalInterest));
			}

			
			$leasingTerm.add($deposit).add($mileage).add('#l_leaseInsurance').add('#l_GapProtection').on('input change', updateLeasing);
			updateLeasing();
			
			// Financing Form
			const $f_vehiclePrice = $('#f_vehiclePrice');
			const $f_financeTerm = $('#f_Term');
			const $f_deposit = $('#f_Deposit');
			const $f_mileage = $('#f_Mileage');
			
			$('#f_RateInsurance').on('change', function () { $(this).val(this.checked ? driver_protection : 0 ); });
			$('#f_gapProtection').on('change', function () { $(this).val(this.checked ? gap_protection : 0 ); });
			
			function updateFinancing() {
				const price = parseFloat($f_vehiclePrice.val()) || 0;
				const term = parseInt($f_financeTerm.val()) || 1;
				let downPayment = parseFloat($f_deposit.val()) || 0;
				const mileagePerYear = parseInt($f_mileage.val()) || 0;
				const totalMileage = mileagePerYear * (term / 12);
				const finalInstallment = price / 2;

				const driverProtectionCost = $('#f_RateInsurance').is(':checked') ? driver_protection : 0;
				const gapProtectionCost = $('#f_gapProtection').is(':checked') ? gap_protection : 0;
				
				// Deposit validation
				const maxDepositPercent = 0.30;
				let maxDeposit = Math.min(price * maxDepositPercent, price);

				if (downPayment > maxDeposit) {
					downPayment = maxDeposit;
					$f_deposit.val(downPayment.toFixed(2));
				}
				
				const netLoan = price - downPayment - finalInstallment;
				const monthlyInterestRate = interest / 12;
				const monthlyRate = (netLoan * monthlyInterestRate) / (1 - Math.pow(1 + monthlyInterestRate, -term));

				// Total  payments (includes interest)
				const totalInstallments = monthlyRate * term;
				const totalInterest = totalInstallments - netLoan;
				const totalAmount = downPayment + totalInstallments + finalInstallment + transfer_cost + driverProtectionCost + gapProtectionCost;
				
				// Update UI
				$('.f_duration').val(`${term} Monate`);
				$('.f_total_mileage').val(`${totalMileage.toLocaleString()} km`);
				$('.f_monthly_installments').val(formatEuro(monthlyRate));
				$('.f_installments_total').val(formatEuro(totalInstallments));
				$('.f_final_installment').val(formatEuro(finalInstallment));
				$('.f_transfer_costs').val(formatEuro(transfer_cost));
				$('.f_total_amount').val(formatEuro(totalAmount));
				$('.f_deposit').val(formatEuro(downPayment));
				$('.f_total_interest').val(formatEuro(totalInterest));
			}

			$f_financeTerm.add($f_deposit).add($f_mileage).add('#f_RateInsurance').add('#f_gapProtection').on('input change', updateFinancing);
			updateFinancing();
			
			$('.calc-form').on('submit', function(e) {
				e.preventDefault();

				let $form = $(this);
				let formData = $form.serialize();
				let $messageDiv = $form.find('.form-message');
    			$messageDiv.text('');
				$(".loader").css("opacity", "1");

				$.ajax({
				  url: '<?php echo admin_url("admin-ajax.php"); ?>',
				  type: 'POST',
				  data: formData + '&action=handle_form_ajax',
				  dataType: 'json',
				  success: function(response) {
					if (response.success) {
					  $messageDiv.css('color', 'green').text(response.data.message);
					} else {
					  $messageDiv.css('color', 'red').text(response.data.message);
					}
				  },
				  complete: function(){ $(".loader").css("opacity", "0"); },
				  error: function() { $messageDiv.css('color', 'red').text('An unexpected error occurred.'); }
				});
			  });
			
			// Toggle the Calculator Form
			$('.toggle-btn').on('click', function () {
				$('.toggle-btn').removeClass('active');
				$(this).addClass('active');
				const target = $(this).data('target');
				$('.calc-form').addClass('hidden');
				$(`#${target}-form`).removeClass('hidden');
			});
		});
	</script>
	<?php
	return ob_get_clean();
}

add_action('wp_ajax_handle_form_ajax', 'handle_form_ajax');
add_action('wp_ajax_nopriv_handle_form_ajax', 'handle_form_ajax');

function handle_form_ajax() {
    $post_data = $_POST;

    // Determine form type
    $is_leasing = isset($post_data['leasing_form']);
    $is_financing = isset($post_data['finance_form']);

    if (!$is_leasing && !$is_financing) { wp_send_json_error(['message' => 'Unknown form type.']); }

    // Common exclusions
    $exclude_keys = ['action', 'leasing_form', 'finance_form'];

    if ($is_leasing) {
        $subject = 'Leasing Form Submission';
        $customer_fields = [ 'Name' => $post_data['l_name'] ?? '', 'Email' => $post_data['l_email'] ?? '', 'Phone' => $post_data['l_phone'] ?? '', 'Message' => $post_data['l_message'] ?? '', ];
        $exclude_keys = array_merge($exclude_keys, ['l_name', 'l_email', 'l_phone', 'l_message']);

        $sections = [
            'Customer Details' => ['l_name', 'l_email', 'l_phone', 'l_message'],
            'Leasing Details' => ['l_vehiclePrice', 'l_Term', 'l_Deposit', 'l_Mileage', 'l_leaseInsurance', 'l_GapProtection', 'l_type', 'l_customer_type', ],
            'Calculation Summary' => [ 'l_vehicle_price', 'l_transfer_costs', 'l_duration', 'l_monthly_installments', 'l_total_mileage', 'l_final_installment', 'l_total_interest', 'l_interest_fixed', 'l_interest_effective', 'l_installments_total', 'l_total_amount',],
        ];

        $email_heading = '🚘 Leasing Request Summary';
        $footer_note = 'This message was generated from your website leasing form.';
    } else {
        // Financing form
        $subject = 'Financing Form Submission';
        $customer_fields = [ 'Name' => $post_data['f_name'] ?? '', 'Email' => $post_data['f_email'] ?? '', 'Phone' => $post_data['f_phone'] ?? '', 'Message' => $post_data['f_message'] ?? '', ];
        $exclude_keys = array_merge($exclude_keys, ['f_name', 'f_email', 'f_phone', 'f_message']);

        $sections = [
            'Customer Details' => ['f_name', 'f_email', 'f_phone', 'f_message'],
            'Financing Details' => [ 'f_vehiclePrice', 'f_Term', 'f_Deposit', 'f_Mileage', 'f_RateInsurance', 'f_gapProtection', 'f_type', 'f_customer_type' ],
            'Calculation Summary' => [ 'f_vehicle_price', 'f_transfer_costs', 'f_duration', 'f_deposit', 'f_monthly_installments', 'f_total_mileage', 'f_final_installment', 'f_total_interest', 'f_interest_fixed', 'f_interest_effective', 'f_installments_total', 'f_total_amount' ],
        ];

        $email_heading = '💰 Financing Request Summary';
        $footer_note = 'This message was generated from your website financing form.';
    }

    // Start building email body
    $email_body = '<html>';
	// $email_body .= '<pre>' . print_r($post_data, true) . '</pre>';
	
    $email_body .= '<body style="margin:0; padding:40px; background-color:#ececf1; font-family:Verdana, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color:#333; line-height:1.5;">
      <div style="max-width:700px; margin:0 auto; background:#ffffff; border-radius:12px; padding:30px 40px; box-shadow:0 4px 16px rgba(0,0,0,0.08);">
        <h2 style="margin-top:0; font-size:24px; color:#333333; margin-bottom:20px;">' . esc_html($email_heading) . '</h2>';

    foreach ($sections as $section_title => $field_keys) {
        $email_body .= '
        <div style="margin-bottom:30px; padding:20px; border:1px solid #e6e6e6; border-radius:10px; background-color:#fff7ec;">
          <h3 style="margin-top:0; margin-bottom:15px; font-size:18px; color:#EF7D00; border-bottom:1px solid #eee; padding-bottom:8px;">' . esc_html($section_title) . '</h3>
          <div style="font-size:0;">';

        foreach ($field_keys as $key) {
            if (isset($post_data[$key])) {
                // Remove form prefix l_ or f_ for label
                $label = ucwords(str_replace(['_', '-'], ' ', preg_replace('/^[lf]_/', '', $key)));
                $value = is_array($post_data[$key]) ? implode(', ', array_map('sanitize_text_field', $post_data[$key])) : sanitize_text_field($post_data[$key]);

                $email_body .= '
                <div style="display:inline-block; width:48%; margin:1%; vertical-align:top; padding:10px 15px; box-sizing:border-box; background:#ffffff; border:1px solid #e0e0e0; border-radius:8px; box-shadow:inset 0 0 5px rgba(0,0,0,0.02);">
                  <div style="font-size:13px; color:#888888;">' . esc_html($label) . '</div>
                  <div style="font-size:15px; font-weight:500; color:#222222;">' . esc_html($value) . '</div>
                </div>';
            }
        }
		$email_body .= '</div></div>';
    }
    $email_body .= ' 
		<div style="margin-top:30px; font-size:13px; color:#aaaaaa; text-align:center;"> <em>' . esc_html($footer_note) . '</em> </div>
      </div>
    </body>
    </html>';

    // Send email
    $to = "lsdev50@gmail.com";
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $mail_sent = wp_mail($to, $subject, $email_body, $headers);

    if ($mail_sent) {
        wp_send_json_success(['message' => 'Thank you! Your form has been submitted successfully.']);
    } else {
        wp_send_json_error(['message' => 'Failed to send email. Please try again later.']);
    }

    wp_die();
}


