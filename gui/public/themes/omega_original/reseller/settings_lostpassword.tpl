<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={THEME_CHARSET}">
<title>{TR_LOSTPW_EMAL_SETUP}</title>
  <meta name="robots" content="noindex">
  <meta name="robots" content="nofollow">
<link href="{THEME_COLOR_PATH}/css/selity.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="{THEME_COLOR_PATH}/css/selity.js"></script>
</head>

<body onload="MM_preloadImages('{THEME_COLOR_PATH}/images/icons/database_a.gif','{THEME_COLOR_PATH}/images/icons/hosting_plans_a.gif','{THEME_COLOR_PATH}/images/icons/domains_a.gif','{THEME_COLOR_PATH}/images/icons/general_a.gif' ,'{THEME_COLOR_PATH}/images/icons/manage_users_a.gif','{THEME_COLOR_PATH}/images/icons/webtools_a.gif','{THEME_COLOR_PATH}/images/icons/statistics_a.gif','{THEME_COLOR_PATH}/images/icons/support_a.gif')">
<!-- BDP: logged_from --><table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td height="20" nowrap="nowrap" class="backButton">&nbsp;&nbsp;&nbsp;<a href="change_user_interface.php?action=go_back"><img src="{THEME_COLOR_PATH}/images/icons/close_interface.png" width="16" height="16" border="0" align="absmiddle"></a> {YOU_ARE_LOGGED_AS}</td>
	  </tr>
	</table>
	<!-- EDP: logged_from -->
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%" style="border-collapse: collapse;padding:0;margin:0;">
	<tr>
		<td style="width: 195px; vertical-align: top;">{MENU}</td>
		<td colspan="2" style="vertical-align: top;"><table style="width: 100%; border-collapse: collapse;padding:0;margin:0;">
		  <tr height="95">
			<td style="padding-left:30px; width: 100%; background-image: url({THEME_COLOR_PATH}/images/top/middle_bg.jpg);">{MAIN_MENU}</td>
			<td style="padding:0;margin:0;text-align: right; width: 73px;vertical-align: top;"><img src="{THEME_COLOR_PATH}/images/top/middle_right.jpg" border="0"></td>
		  </tr>
		  <tr>
			<td colspan="3"><table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
				<td align="left"><table width="100%" cellpadding="5" cellspacing="5">
					<tr>
					  <td width="25"><img src="{THEME_COLOR_PATH}/images/content/table_icon_email.png" width="25" height="25"></td>
					  <td colspan="2" class="title">{TR_LOSTPW_EMAIL}</td>
					</tr>
				</table></td>
				<td width="27" align="right">&nbsp;</td>
			  </tr>
			  <tr>
				<td><form action="settings_lostpassword.php" method="post" name="frmlostpassword" ID="frmlostpassword">
					<table width="100%" cellpadding="5" cellspacing="5">
					  <tr>
						<td width="25">&nbsp;</td>
						<td colspan="4" class="content3"><b>{TR_MESSAGE_TEMPLATE_INFO}</b></td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td width="48%" colspan="2" class="content3">{TR_ACTIVATION_EMAIL}</td>
						<td colspan="2" class="content3">{TR_PASSWORD_EMAIL}</td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td width="24%" class="content2">{TR_USER_LOGIN_NAME}</td>
						<td width="24%" class="content">{USERNAME}</td>
						<td width="24%" class="content2">{TR_USER_LOGIN_NAME}</td>
						<td class="content">{USERNAME}</td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td class="content2">{TR_LOSTPW_LINK}</td>
						<td class="content">{LINK}</td>
						<td class="content2">{TR_USER_PASSWORD}</td>
						<td class="content">{PASSWORD}</td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td class="content2">{TR_USER_REAL_NAME}</td>
						<td class="content">{NAME}</td>
						<td class="content2">{TR_USER_REAL_NAME}</td>
						<td class="content">{NAME}</td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td class="content2">{TR_BASE_SERVER_VHOST}</td>
						<td class="content">{BASE_SERVER_VHOST}</td>
						<td class="content2">{TR_BASE_SERVER_VHOST}</td>
						<td class="content">{BASE_SERVER_VHOST}</td>
					  </tr>
					</table>
				  <br>
					<table width="100%" cellpadding="5" cellspacing="5">
					  <tr>
						<td width="25">&nbsp;</td>
						<td colspan="3" class="content3"><b>{TR_MESSAGE_TEMPLATE}</b></td>
					  </tr>
					  <!-- BDP: page_message -->
					  <tr>
						<td>&nbsp;</td>
						<td colspan="3" class="title"><span class="message">{MESSAGE}</span></td>
					  </tr>
					  <!-- EDP: page_message -->
					  <tr>
						<td>&nbsp;</td>
						<td class="content2" width="200">{TR_SUBJECT}</td>
						<td class="content" width="35%"><input name="subject1" type="text" class="textinput" ID="subject1" style="width:90%" value="{SUBJECT_VALUE1}"></td>
						<td class="content" width="35%"><input type="text" name="subject2" value="{SUBJECT_VALUE2}" style="width:90%" class="textinput"></td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td class="content2" style="width:200px;vertical-align:top;">{TR_MESSAGE}</td>
						<td class="content" width="35%"><textarea name="message1" rows="20" class="textinput2" ID="message1" style="width:90%">{MESSAGE_VALUE1}</textarea></td>
						<td class="content" width="35%"><textarea name="message2" rows="20" class="textinput2" ID="message2" style="width:90%">{MESSAGE_VALUE2}</textarea></td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td width="200" class="content2">{TR_SENDER_EMAIL}</td>
						<td COLSPAN="2" class="content">{SENDER_EMAIL_VALUE}
						  <input type="hidden" name="sender_email" value="{SENDER_EMAIL_VALUE}" style="width:270px" class="textinput">
						</td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td width="200" class="content2">{TR_SENDER_NAME}</td>
						<td COLSPAN="2" class="content">{SENDER_NAME_VALUE}
						  <input type="hidden" name="sender_name" value="{SENDER_NAME_VALUE}" style="width:270px" class="textinput">
						</td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td colspan="3"><input name="Submit" type="submit" class="button" value="{TR_APPLY_CHANGES}"></td>
					  </tr>
					</table>
				  <input type="hidden" name="uaction" value="apply">
				</form></td>
				<td>&nbsp;</td>
			  </tr>
			  <tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			  </tr>
			</table></td>
		  </tr>
		</table></td>
	</tr>
</table>
</body>
</html>
