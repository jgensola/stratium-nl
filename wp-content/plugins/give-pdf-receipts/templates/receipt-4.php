<?php
/**
 * Give PDF Receipt Template
 *
 * Receipt - 4
 */
?>
<table id="light_gray" class="light_gray" style="margin: 0; padding: 0; color: #333; background-color: #ededed;" border="0" width="100%">
	<tbody>
	<tr>
		<td width="100%">
			<table style="font-family: Helvetica; margin: 0; padding: 0;" border="0" width="100%">
				<tbody>
				<tr>
					<td style="font-size: 12px;color: #777;  font-style: italic; line-height: 40px;" align="center" valign="middle" width="100%">Donation Receipt: Your Tagline Here</td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	<tr>
		<td width="100%">
			<table style="margin: 0; padding: 0;" border="0" width="100%">
				<tbody>
				<tr>
					<td class="width_27_5" width="15%"></td>
					<td class="width_45" align="center" width="70%">
						<table style="margin: 0; padding: 0; background-color: #fff; border: 1px solid #dedede;" border="0" width="100%">
							<tbody>
							<tr>
								<td width="100%">
									<table style="background-color: #e5e5e5; margin: 0; padding: 0;" border="0" width="100%">
										<tbody>
										<tr>
											<td width="100%"></td>
										</tr>
										<tr>
											<td align="center" valign="middle" width="100%">
												<img class="alignnone size-medium wp-image-14" src="%assets_url%/images/logo_placeholder.png" alt=""
												     width="300" height="127" />
											</td>
										</tr>
										<tr>
											<td width="100%"></td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td width="100%">
									<table style="font-family: Helvetica; margin: 0; padding: 0;" border="0" width="100%">
										<tbody>
										<tr>
											<td colspan="3" align="center" width="100%"></td>
										</tr>
										<tr>
											<td width="5%"></td>
											<td style="font-size: 16px; line-height: 1.50;" align="left" width="90%">Dear {first_name},</td>
											<td width="5%"></td>
										</tr>
										<tr>
											<td colspan="3" align="center" width="100%"></td>
										</tr>
										<tr>
											<td width="5%"></td>
											<td style="font-size: 16px; line-height: 1.50;" align="left" width="90%">Your donation for {donation_name} via
												<a style="color: #51aef0; text-decoration: none;" href="http://mywebsite.org">http://mywebsite.org</a> on {date} is greatly appreciated.
												This is an official tax receipt issued by Organization Name
												- a non-profit organization.</td>
											<td width="5%"></td>
										</tr>
										<tr>
											<td colspan="3" align="center" width="100%"></td>
										</tr>
										<tr>
											<td width="5%"></td>
											<td style="font-size: 16px; line-height: 1.50; font-weight:bold;" align="left" width="90%">INVOICE TO:</td>
											<td width="5%"></td>
										</tr>
										<tr>
											<td width="5%"></td>
											<td style="font-size: 16px; line-height: 1.50;" align="left" width="90%">
												{full_name}
											</td>
											<td width="5%"></td>
										</tr>
										<tr>
											<td width="5%"></td>
											<td style="font-size: 16px; line-height: 1.50;" align="left" width="90%">
												{billing_address}
											</td>
											<td width="5%"></td>
										</tr>
										<tr>
											<td colspan="3" align="center" width="100%"></td>
										</tr>
										<tr>
											<td width="5%"></td>
											<td style="font-size: 20px; font-weight: bold; line-height: 1.50;" align="left" width="90%">
												Receipt of Charitable Donation:
											</td>
											<td width="5%"></td>
										</tr>
										<tr>
											<td colspan="3" align="center" width="100%"></td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td width="100%">
									<table style="margin: 0; padding: 0; background-color: #f8f8f8; border-top: 1px solid #E6E6E6; border-bottom: 1px solid #E6E6E6;" border="0" width="100%">
										<tbody>
										<tr>
											<td colspan="3" width="5%"></td>
											<td width="90%">
												<table style="margin: 0; padding: 0; background-color: #f8f8f8;" border="0" width="100%">
													<tbody>
													<tr>
														<td width="100%"></td>
													</tr>
													<tr>
														<td style="font-family: Helvetica; font-size: 16px; line-height: 1.50;" align="left" valign="middle" width="100%"><strong>DONATION NAME:</strong> {donation_name}</td>
													</tr>
													<tr>
														<td style="font-size: 16px;" align="left" valign="middle" width="100%"><strong style="font-family: Helvetica;">DONATION AMOUNT:</strong> {amount}</td>
													</tr>
													<tr>
														<td style="font-family: Helvetica; font-size: 16px; line-height: 1.50;" align="left" valign="middle" width="100%"><strong>PAYMENT METHOD:</strong> {payment_method}</td>
													</tr>
													<tr>
														<td style="font-family: Helvetica; font-size: 16px; line-height: 1.50;" align="left" valign="middle" width="100%"><strong>DONATION STATUS:</strong> {payment_status}</td>
													</tr>
													<tr>
														<td style="font-family: Helvetica; font-size: 16px; line-height: 1.50;" align="left" valign="middle" width="100%"><strong>DONATION DATE:</strong> {date}</td>
													</tr>
													<tr>
														<td style="font-family: Helvetica; line-height: 1.50; font-size: 16px;" align="left" valign="middle" width="100%"><strong>PAYMENT ID:</strong> {payment_id}</td>
													</tr>
													<tr>
														<td style="font-family: Helvetica; line-height: 1.50; font-size: 16px;" align="left" valign="middle" width="100%"><strong>TRANSACTION ID:</strong> {transaction_id}</td>
													</tr>
													<tr>
														<td style="font-family: Helvetica; line-height: 1.50; font-size: 16px;" align="left" valign="middle" width="100%"><strong>TRANSACTION KEY:</strong> {transaction_key}</td>
													</tr>
													<tr>
														<td width="100%"></td>
													</tr>
													</tbody>
												</table>
											</td>
											<td colspan="3" width="5%"></td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td width="100%">
									<table style="font-family: Helvetica; margin: 0; padding: 0; border-top: 1px solid #dedede;" border="0" width="100%">
										<tbody>
										<tr>
											<td colspan="3" width="100%"></td>
										</tr>
										<tr>
											<td width="5%"></td>
											<td style="text-align: justify; font-size: 16px; line-height: 1.50;" width="90%">Cepit pro zonae fixo quia iunctarum. Triones deus circumdare siccis fulgura animalia. Septemque sine parte. Melioris habitabilis naturae mundi campoque persidaque pontus bracchia terram. Fronde flamma.</td>
											<td width="5%"></td>
										</tr>
										<tr>
											<td colspan="3" width="100%"></td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
					</td>
					<td class="width_27_5" width="15%"></td>
				</tr>
				<tr>
					<td class="width_27_5" width="15%"></td>
					<td class="width_45" align="center" width="70%">
						<table style="font-family: Helvetica; margin: 0; line-height: 1.50; padding: 0;" border="0" width="100%">
							<tbody>
							<tr>
								<td width="100%"></td>
							</tr>
							<tr>
								<td style="text-align: center; font-size: 16px; padding: 0 15px;" width="100%">123 Street Address</td>
							</tr>
							<tr>
								<td style="text-align: center; font-size: 16px; padding: 0 15px;" width="100%">City, State, Zip</td>
							</tr>
							<tr>
								<td style="text-align: center; font-size: 16px; padding: 0 15px;" width="100%">Tel: 1.888.555.5555</td>
							</tr>
							<tr>
								<td width="100%"></td>
							</tr>
							<tr>
								<td style="text-align: center;font-weight: bold; font-size: 16px; padding: 0 15px;" width="100%">Website:
								</td>
							</tr>
							<tr>
								<td style="color: #aeaeae;text-align: center; font-size: 16px; padding: 0 15px;" width="100%"><a style="color: #aeaeae;" href="http://mywebsite.org">http://mywebsite.org</a></td>
							</tr>
							<tr>
								<td width="100%"></td>
							</tr>
							<tr>
								<td style="text-align: center; font-weight: bold;  font-size: 16px; padding: 0 15px;" width="100%">Email:
								</td>
							</tr>
							<tr>
								<td style="text-align: center; font-size: 16px; padding: 0 15px;" width="100%"><a style="color: #aeaeae;" href="mailto:email@hotmail.com">email@hotmail.com</a></td>
							</tr>
							<tr>
								<td width="100%"></td>
							</tr>
							</tbody>
						</table>
					</td>
					<td class="width_27_5" width="15%"></td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>