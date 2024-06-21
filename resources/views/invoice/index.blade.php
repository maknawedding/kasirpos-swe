<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

		<title>INVOICE_{{$order->name}}</title>

		<!-- Favicon -->
		<link rel="icon" href="/images/favicon_makna.png" type="image/x-icon" />

		<!-- Invoice styling -->
		<style>
			body {
				font-family: 'Century Gothic', 'Helvetica', Helvetica, Arial, sans-serif;
				text-align: center;
				color: #777;
			}

			body h1 {
				font-weight: 300;
				margin-bottom: 0px;
				padding-bottom: 0px;
				color: #000;
			}

			body h3 {
				font-weight: 100;
				margin-top: 10px;
				margin-bottom: 20px;
				font-style: italic;
				color: #555;
				font-size: 12px;
			}

			body a {
				color: #06f;
			}

			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 13px;
				line-height: 24px;
				font-family: 'Century Gothic', 'Helvetica', Helvetica, Arial, sans-serif;
				color: #555;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
				border-collapse: collapse;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}
            .watermark {
				position: absolute;
				top: 23%;
				left: 50%;
				transform: translate(-50%, -50%);
				font-size: 70px;
				color: rgba(16, 117, 240, 0.055);
				white-space: nowrap;
				z-index: -1;
				user-select: none;
				pointer-events: none;
            }
		</style>
	</head>

	<body>
    <div class="watermark">
			<b>{{$order->paid_date ? 'LUNAS' : 'BELUM LUNAS'}}</b>
		</div>
    <br />
    <br />
		<div class="invoice-box">
			<table>
				<tr class="top">
					<td colspan="2">
						<table>
							<tr>
								<td class="title">
									<img src="/images/logomki.png" alt="Company logo" style="width: 100%; max-width: 300px" />
								</td>

								<td>
									Invoice #: 00{{$order->id}}<br />
									Created : {{$order->issued_date}}<br />
									Due : {{$order->due_date}}<br />
                                    Paid : {{$order->paid_date}}
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2">
						<table>
							<tr>
								<td>
                                    Kepada Yth. Bpk/Ibu :<br />
                                    <b>{{$order->name}}</b><br />
									No. Tlp : {{$order->phone}}<br />
									No. Tenant : {{$order->no_tenant}}
								</td>

								<td>
                                    <b>PT. Makna Kreatif Indonesia</b><br />
									Makna Wedding & Event Planner<br />
									Jl. Basuki Rahmat, Belakang Masjid Al Falah Palembang
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="heading">
					<td>Payment Method</td>
					<td>Total</td>
				</tr>

				<tr class="details">
					<td>{{$order->paymentMethod->name}}<br />
                        No. Rek : {{$order->paymentMethod->no_rekening}}<br />
                    </td>

					<td>
                        Rp. {{number_format($order->total_price)}}
                    </td>
				</tr>

				<tr class="heading">
					<td>Item</td>
					<td>Bayar</td>
				</tr>

				<tr class="item">
					<td>
                        Pembayaran : {{$order->issued_date}}<br />
                        Rekanan : {{$order->notes}}<br />
                        Keterangan Lain : {{$order->keterangan}}<br />
                    </td>

					<td>
                        Rp. {{number_format($order->paid_amount)}}<br />
                    </td>
				</tr>

				<tr class="item last">
				</tr>

				<tr class="heading">
					<td>Sisa Pembayaran</td>
					<td>Rp. {{number_format($order->change_amount)}}</td>
				</tr>

                <tr class="item">
                    <td>
                        <H3>
							<b>No. Rek Pembayaran :</b><br/>
							1. PT. Bank Mandiri, Tbk (113-00-5151-115) an. PT. Makna Kreatif Indonesia<br/>
							2. PT. Bank Mandiri, Tbk (112-00-7744-4474) an. CV. Rumah Desain Production
						</H3>
                    </td>
				</tr>
                
			</table>
		</div>
	</body>
</html>