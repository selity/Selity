<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={THEME_CHARSET}">
<title>{TR_RESELLER_MAIN_INDEX_PAGE_TITLE}</title>
  <meta name="robots" content="noindex">
  <meta name="robots" content="nofollow">
<link href="{THEME_COLOR_PATH}/css/selity.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="{THEME_COLOR_PATH}/css/selity.js"></script>
<script type="text/javascript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>
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
					  <td width="25"><img src="{THEME_COLOR_PATH}/images/content/table_icon_tools.png" width="25" height="25"></td>
					  <td colspan="2" class="title">{TR_MENU_ORDER_SETTINGS}</td>
					</tr>
				</table></td>
				<td width="27" align="right">&nbsp;</td>
			  </tr>
			  <tr>
				<td><form name="edit_hfp" method="post" action="order_settings.php">
					<table width="100%" cellpadding="5" cellspacing="5">
					  <!-- BDP: page_message -->
					  <tr>
						<td>&nbsp;</td>
						<td class="title"><span class="message">{MESSAGE}</span></td>
					  </tr>
					  <!-- EDP: page_message -->
					  <tr>
						<td>&nbsp;</td>
						<td class="content2"><strong>{TR_IMPLEMENT_INFO}</strong></td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td class="content">{TR_IMPLEMENT_URL}</td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td class="content2"><strong>{TR_HEADER}</strong></td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td class="content"><textarea name="header" rows="15" cols="80" class="textinput2" id="header" style="width:90%"><!-- BDP: purchase_header -->
				<!-- EDP: purchase_header -->
			</textarea></td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td class="content2"><strong>{TR_FOOTER}</strong></td>
					  </tr>
					  <tr>
						<td width="25">&nbsp;</td>
						<td class="content"><textarea name="footer" rows="15" cols="80" class="textinput2" id="footer" style="width:90%"><!-- BDP: purchase_footer -->
				<!-- EDP: purchase_footer -->
			</textarea></td>
					  </tr>
					  <tr>
						<td>&nbsp;</td>
						<td><input name="Submit" type="submit" class="button" value="{TR_APPLY_CHANGES}">
						  &nbsp;&nbsp;
						  <input name="Button" type="button" class="button" onclick="MM_openBrWindow('/orderpanel/','preview','width=770,height=480')" value="{TR_PREVIEW}"></td>
					  </tr>
					</table>
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
