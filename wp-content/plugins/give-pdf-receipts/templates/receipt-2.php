<?php
/**
 * Give PDF Receipt Template
 *
 * Receipt - 2
 */
?>
<table id="night_white" class="night_white" style="margin: 0; padding: 0; color: #333; height: 100%; background-color: #3d3d3d; max-height: 100%; min-height: 100%;" border="0" width="100%" cellspacing="0" cellpadding="0">
	<tbody>
	<tr>
		<td width="100%">
			<table style="margin: 0; padding: 15px 0 20px;" border="0" width="100%">
				<tbody>
				<tr>
					<td style="font-family: times; font-weight: 400; background-color: #3d3d3d; font-size: 14px; color: #fff; font-style: italic; line-height: 40px;" align="center" valign="middle" width="100%">Your tagline here</td>
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
									<table style="margin: 0; padding: 0 30px;" border="0" width="100%">
										<tbody>
										<tr>
											<td align="center" width="100%"></td>
										</tr>
										<tr>
											<td align="left" valign="middle" width="100%"><img class="alignnone size-medium wp-image-13" src="%assets_url%/images/logo_placeholder.png" alt="" width="300" height="127" /></td>
										</tr>
										<tr>
											<td align="center" width="100%"></td>
										</tr>
										<tr>
											<td style="font-weight: 400; font-family: times; font-size: 18px; line-height: 30px;" align="left" width="100%">Dear {full_name},</td>
										</tr>
										<tr>
											<td align="center" width="100%"></td>
										</tr>
										<tr>
											<td style="font-family: times; font-weight: 400; font-size: 18px; line-height:1.45;" align="left" width="100%">Your donation for
												{donation_name} on
												<a href="#">http://website.com</a> on {date} is greatly appreciated.
												This is an official tax receipt issued by Organization Name
												- a non-profit organization.</td>
										</tr>
										<tr>
											<td align="center" width="100%"></td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td style="font-family: times; background-color: #fff; font-size: 21px; color: #333; font-weight: bold; line-height: 40px;" align="center" valign="middle" width="100%">Receipt of Charitable Donation</td>
							</tr>
							<tr>
								<td align="center" width="100%"></td>
							</tr>
							<tr>
								<td width="100%">
									<table style="margin: 0; padding: 0; background-color: #f8f8f8;" border="0" width="100%">
										<tbody>
										<tr>
											<td width="100%">
												<table style="margin: 0; padding: 0 30px; background-color: #f8f8f8; font-size: 16px;" border="0" width="100%">
													<tbody>
													<tr>
														<td width="100%"></td>
													</tr>
													<tr>
														<td width="100%">
															<table style="margin: 0; padding: 0; background-color: #f8f8f8;" border="0" width="100%">
																<tbody>
																<tr>
																	<td style="font-size: 16px;" align="left" valign="middle" width="60%">
																		<table style="margin: 0; padding: 0; background-color: #f8f8f8;" border="0" width="100%">
																			<tbody>
																			<tr>
																				<td style="font-family: times; font-size: 16px;" align="left" valign="middle" width="100%"><strong>DONATION NAME:</strong>
																					{donation_name}</td>
																			</tr>
																			<tr>
																				<td style="font-size: 16px;" align="left" valign="middle" width="100%"><strong style="font-family: times;">DONATION AMOUNT:</strong>
																					{amount}</td>
																			</tr>
																			<tr>
																				<td style="font-family: times; font-size: 16px;" align="left" valign="middle" width="100%"><strong>PAYMENT METHOD:</strong>
																					{payment_method}</td>
																			</tr>
																			<tr>
																				<td style="font-family: times; font-size: 16px;" align="left" valign="middle" width="100%"><strong>DONATION STATUS:</strong>
																					{payment_status}</td>
																			</tr>
																			<tr>
																				<td style="font-family: times; font-size: 16px;" align="left" valign="middle" width="100%"><strong>DONATION DATE:</strong>
																					{date}</td>
																			</tr>
																			</tbody>
																		</table>
																	</td>
																	<td style="font-family: times; font-size: 16px;" align="left" valign="middle" width="40%">
																		<table style="margin: 0; padding: 0; background-color: #f8f8f8;" border="0" width="100%">
																			<tbody>
																			<tr>
																				<td style="font-size: 16px; font-weight: bold;" align="left" valign="middle" width="100%">INVOICE TO:</td>
																			</tr>
																			<tr>
																				<td style="font-size: 16px;" align="left" valign="middle" width="100%">{full_name}</td>
																			</tr>
																			<tr>
																				<td style="font-size: 16px;" align="left" valign="middle" width="100%">{billing_address}</td>
																			</tr>
																			</tbody>
																		</table>
																	</td>
																</tr>
																</tbody>
															</table>
														</td>
													</tr>
													<tr>
														<td width="100%">
															<table style="font-family: times; margin: 0; padding: 0; background-color: #f8f8f8;" border="0" width="100%">
																<tbody>
																<tr>
																	<td style="font-size: 16px;" align="left" valign="middle" width="100%"><strong>PAYMENT ID:</strong> {payment_id}</td>
																</tr>
																</tbody>
															</table>
														</td>
													</tr>
													<tr>
														<td width="100%">
															<table style="font-family: times; margin: 0px; padding: 0px; background-color: #f8f8f8;" border="0" width="100%">
																<tbody>
																<tr>
																	<td style="font-size: 16px;" align="left" valign="middle" width="100%"><strong>TRANSACTION KEY:</strong> {transaction_key}</td>
																</tr>
																</tbody>
															</table>
														</td>
													</tr>
													<tr>
														<td width="100%"></td>
													</tr>
													</tbody>
												</table>
											</td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td width="100%">
									<table style="font-family: times; margin: 0; padding: 0 30px; border-top: 1px solid #dedede;" border="0" width="100%">
										<tbody>
										<tr>
											<td width="100%"></td>
										</tr>
										<tr>
											<td style="font-weight: 400; text-align: justify; font-size: 18px; line-height: 1.45;" width="100%">Cepit pro zonae fixo quia
												iunctarum. Triones deus
												circumdare
												siccis fulgura animalia. Septemque sine parte. Melioris habitabilis
												naturae mundi campoque persidaque pontus bracchia terram.
												Fronde flamma.</td>
										</tr>
										<tr>
											<td width="100%"></td>
										</tr>
										<tr>
											<td style="text-align: justify; font-size: 18px;" width="100%">Sincerely,</td>
										</tr>
										<tr>
											<td width="100%"></td>
										</tr>
										<tr>
											<td style="text-align: justify; font-size: 18px;" width="100%"><img style="margin-top: 10px;" src="%assets_url%/images/signature_placeholder.png" /></td>
										</tr>
										<tr>
											<td width="100%"></td>
										</tr>
										<tr>
											<td style="text-align: justify; font-size: 18px;" width="100%">B. FRANKLIN</td>
										</tr>
										<tr>
											<td style="text-align: justify; font-size: 18px;" width="100%">President, Organization Name</td>
										</tr>
										<tr>
											<td width="100%"></td>
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
					<td class="width_45" style="font-family: times; color: #fff;" align="center" width="70%">
						<table style="font-size: 14px; margin: 0; padding: 0; border-top: 1px solid #dedede; color: #fff;" border="0" width="100%">
							<tbody>
							<tr>
								<td style="width: 100%; text-align: center;"></td>
							</tr>
							<tr>
								<td style="font-size: 14px; width: 100%; text-align: center; font-style: italic;">123 Street Address</td>
							</tr>
							<tr>
								<td style="font-size: 14px; width: 100%; text-align: center; font-style: italic;">City, State, Zip</td>
							</tr>
							<tr>
								<td style="font-size: 14px; width: 100%; text-align: center; font-style: italic;">Tel: 1.888.555.5555</td>
							</tr>
							<tr>
								<td style="width: 100%; text-align: center;"></td>
							</tr>
							<tr>
								<td style="font-size: 14px; width: 100%; text-align: center;">Website:</td>
							</tr>
							<tr>
								<td style="font-size: 14px; width: 100%; text-align: center;"><a style="color: #aeaeae; text-decoration: none;" href="http://mywebsite.org">http://mywebsite.org</a></td>
							</tr>
							<tr>
								<td style="width: 100%; text-align: center;"></td>
							</tr>
							<tr>
								<td style="font-size: 14px; width: 100%; text-align: center;">Email:</td>
							</tr>
							<tr>
								<td style="font-size: 14px; width: 100%; text-align: center;"><a style="color: #aeaeae; text-decoration: none;" href="mailto:email@hotmail.com">email@hotmail.com</a></td>
							</tr>
							<tr>
								<td style="width: 100%; text-align: center;"></td>
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