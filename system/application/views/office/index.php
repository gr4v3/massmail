<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <title>The document title</title>
  </head>
  <body>
<style type="text/css">
	.extra_width_600 {
		width:800px;
	}
</style>
<div class="dialog white_box">
	<div class="title">Mail Server Config</div>
	<div class="content">
		<form name="home" method="post" action="office/input">

			<table>
				<caption>Select one of the following sections.</caption>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'server');" value="servers" /></td>
				</tr>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'account');" value="accounts" /></td>
				</tr>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'mailing');" value="distribution" /></td>
				</tr>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_system_menu');" value="mailing" /></td>
				</tr>
				<tr>
					<td class="control">
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="execute" value="next" />
					</td>
				</tr>
			</table>

		</form>
	</div>
</div>

<div class="dialog white_box">
	<div class="title">Server Status</div>
	<div class="content">
		<form name="status" method="post" action="office/input">

			<table id="mailing_status">
				<tr>
					<td class="center">mailing</td>
                                        <td class="center"><img class="mailing" src="../img/no.png" /></td>
				</tr>
                                <tr>
					<td class="center">sending</td>
                                        <td class="center"><img class="sending" src="../img/no.png" /></td>
				</tr>
                                <tr>
					<td class="center">replies</td>
                                        <td class="center"><img class="replies" src="../img/no.png" /></td>
				</tr>
                                <tr>
					<td class="center">bounces</td>
                                        <td class="center"><img class="bounces" src="../img/no.png" /></td>
				</tr>
				<tr>
					<td class="control" colspan="2">
						<input type="hidden" name="task" value="" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="hidden" name="execute" value="next" />
					</td>
				</tr>
			</table>

		</form>
	</div>
</div>



<div class="dialog white_box">
	<div class="title">Server Management</div>
	<div class="content">
		<form name="server" method="post" action="office/input">

			<table>
				<caption>Select one of the following operations.</caption>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'new_server','add_server');" value="new" /></td>
				</tr>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'active_servers','new_server');" value="edit" /></td>
				</tr>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'all_servers','lockunlock_server');" value="lock/unlock" /></td>
				</tr>
				<tr>
					<td class="control">
						<input type="hidden" name="task" value="" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="hidden" name="execute" value="next" />
					</td>
				</tr>
			</table>

		</form>
	</div>
</div>




<div class="dialog white_box">
	<div class="title">Account Management</div>
	<div class="content">
		<form name="account" method="post" action="office/input">
			<table>
				<caption>Select one of the following operations.</caption>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'active_servers','select_ip');" value="new" /></td>
				</tr>
				<!--
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'active_accounts','new_account');" value="edit" /></td>
				</tr>
				-->
				<tr>
					<td class="center">&nbsp;</td>
				</tr>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'num_accounts',false);" value="stats" /></td>
				</tr>
				<tr>
					<td class="control">
						<input type="hidden" name="task" value="" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="hidden" name="execute" value="next" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>


<div class="dialog white_box">
	<div class="title">Select IP</div>
	<div class="content">
		<form name="select_ip" method="post" action="office/input">

			<table>
				<caption>Select one of the following ip to associate with the mail account.</caption>
				<tr>
					<td class="center">
						<select name="ip" multiple="true"></select>
						<!-- <input type="button" class="button"  onclick="javascript:redirect(this,'block_ip',false);"  value="block" /> -->
					</td>
				</tr>
				<tr>
					<td class="control">
						<input type="hidden" name="task" value="choose_ip" />
						<input type="button" class="button"  name="back" value="return" />

						<input type="button" class="button"  name="execute" value="next" />
					</td>
				</tr>

			</table>

		</form>
	</div>
</div>


<div class="dialog white_box">
	<div class="title">Active servers</div>
	<div class="content">
		<form name="active_servers" method="post" action="office/input">
			<table>
				<caption>Select one of the following servers.</caption>
				<tr>
					<td class="center">
						<select name="server" multiple="true"></select>
					</td>
				</tr>
				<tr>
					<td class="control">
						<input type="hidden" name="task" value="" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="button" class="button"  name="execute" value="next" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>

<div class="dialog white_box">
	<div class="title">lock/unlock servers</div>
	<div class="content">
		<form name="all_servers" method="post" action="office/input">
			<table>
				<caption>Select one of the following servers.</caption>
				<tr>
					<td class="center">
						<select name="server" multiple="true"></select>
					</td>
				</tr>
				<tr>
					<td class="control">
						<input type="hidden" name="task" value="" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="button" class="button"  name="execute" value="next" />
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>



<div class="dialog white_box">
	<div class="title">Active Accounts</div>
	<div class="content">
		<form name="active_accounts" method="post" action="office/input">

			<table>
				<caption>Select one of the following accounts.</caption>
				<tr>
					<td class="center">
						<select name="account" multiple="true"></select>
					</td>
				</tr>
				<tr>
					<td class="control">
						<input type="hidden" name="task" value="" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="button" class="button"  name="execute" value="next" />
					</td>
				</tr>

			</table>

		</form>
	</div>
</div>



<div class="dialog white_box">
	<div class="title">Mail Account Specification</div>
	<div class="content">
		<form id="new_account" name="new_account" method="post" action="office/input">

			<table>
				<caption>Insert your account details.</caption>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td class="label">Account using server </td>
					<td><input type="hidden" name="host_id" value="" /><input type="text" name="servername" value="" disabled="disabled" /></td>
				</tr>
				<tr>
					<td class="label">Account using ip </td>
					<td>
						<span id="ip"></span>&nbsp;&nbsp;<span id="country"></span>
					</td>
				</tr>
				<tr>
					<td class="label">Change account ip proxy </td>
					<td class="ip">
						<input type="text" name="ip" value="" />
					</td>
				</tr>
				<tr>
					<td class="label">Click here to open register form</td>
					<td>
						<a class="url" href="" target="_blank">register</a>
					</td>
				</tr>
				<tr>
					<td class="label">Click here to go to login page</td>
					<td>
						<a class="loginurl" href="" target="_blank">login</a>
					</td>
				</tr>


				<tr>
					<td class="label">First Name</td>
					<td><input type="text" name="firstname" value="Promo" /></td>
				</tr>
				<tr>
					<td class="label">Last Name</td>
					<td><input type="text" name="lastname" value="News" /></td>
				</tr>
				<!--
				<tr>
					<td class="label">Name</td>
					<td><input type="text" name="name" value="Promo News" /></td>
				</tr>
				<tr>
					<td class="label">Generate random login details</td>
					<td><input type="button" class="button" name="randomlogin" value="generate" /></td>
				</tr>
				-->
				<tr>
					<td class="label">Account Email</td>
					<td><input type="text" name="email" value="" /></td>
				</tr>
				<!--
				<tr>
					<td class="label">
						Account ID
						<div class="small">in some servers the username is diferent to the current email address</div>
					</td>
					<td>
						<input type="text" name="login" value="" /><br />
					</td>
				</tr>
				-->
				<tr>
					<td class="label">Account Login</td>
					<td><input type="text" name="login" value="" /></td>
				</tr>
				<tr>
					<td class="label">Account Pass</td>
					<td><input type="text" name="pass" value="" /></td>
				</tr>
				<tr>
					<td class="label">Alternative Email</td>
					<td><input type="text" name="altemail" value="" /></td>
				</tr>
				<tr>
					<td class="label"><img id="captcha_img" /></td>
					<td valign="middle"><input id="captcha" type="text" name="captcha" value="" /></td>
				</tr>
				<tr>
					<td colspan="2" class="control">
						<input type="hidden" name="tunnel" value="" />
						<input type="hidden" name="login_id" value="" />
						<input type="hidden" name="task" value="add_account" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="button" class="button"  name="execute" value="next" />
					</td>
				</tr>
			</table>

		</form>
	</div>
</div>


<div class="dialog white_box extra_width_600">
	<div class="title">Mail Server Specification</div>
	<div class="content">
		<form class="new_server" name="new_server" method="post" action="office/input">

			<table>
				<caption>Insert your mail server details.</caption>
				<tr>
					<td class="label">Host Name</td>
					<td align="left"><input type="text" name="hostname" value="" /></td>
				</tr>
				<tr>
					<td class="label">Country (initials)</td>
					<td align="left"><input type="text" name="country" value="" /></td>
				</tr>
				<tr>
					<td>
						<table class="sub export">
							<caption>export settings</caption>
							<tr>
								<td class="label">address</td>
								<td><input type="text" class="host" name="export[host]" value="" /></td>
							</tr>
							<tr>
								<td class="label">port</td>
								<td><input type="text" class="port" name="export[port]" value="25" /></td>
							</tr>
							<tr>
								<td class="label">timeout</td>
								<td><input type="text" class="timeout" name="export[timeout]" value="30" /></td>
							</tr>
							<tr>
								<td class="label">service flags</td>
								<td><input type="text" class="service_flags" name="export[service_flags]" value="" /></td>
							</tr>
							<tr>
								<td class="label">custom headers</td>
								<td>
									<textarea class="headers" name="export[headers]"></textarea>
								</td>
							</tr>
							<tr>
								<td class="label">handler</td>
								<td>
									<input type="radio" name="export[handler]" checked="true" value="swift" />swift
									<input type="radio" name="export[handler]" value="hotmailer" />hotmailer
								</td>
							</tr>
						</table>



						<table class="sub import">
							<caption>import settings</caption>
							<tr>
								<td class="label">protocol</td>
								<td>
									<input type="radio" name="import[type]" checked="true" value="pop" />pop
									<input type="radio" name="import[type]" value="imap" />imap
								</td>
							</tr>
							<tr><td colspan="2">&nbsp;</td></tr>
							<tr>
								<td class="label">address</td>
								<td><input type="text" class="host" name="import[host]" value="" /></td>
							</tr>
							<tr>
								<td class="label">port</td>
								<td><input type="text" class="port" name="import[port]" value="" /></td>
							</tr>
							<tr>
								<td class="label">timeout</td>
								<td><input type="text" class="timeout" name="import[timeout]" value="100" /></td>
							</tr>
							<tr>
								<td class="label">service flags</td>
								<td><input type="text" class="service_flags" name="import[service_flags]" value="" /></td>
							</tr>
							<tr>
								<td class="label">folder</td>
								<td><input type="text" class="inbox" name="import[inbox]" value="INBOX" /></td>
							</tr>
						</table>
					</td>
					<td>

						<table class="sub">
							<caption>mailing rules</caption>
							<tr>
								<td class="label">flood refresh</td>
								<td><input type="text" name="flood_refresh" value="" /></td>
							</tr>
							<tr>
								<td class="label">flood interval (microseconds)</td>
								<td><input type="text" name="flood_interval" value="" /></td>
							</tr>
							<tr>
								<td class="label">flood sleep (microseconds)</td>
								<td><input type="text" name="flood_sleep" value="" /></td>
							</tr>
							<!--
							<tr>
								<td class="label">throttler mode</td>
								<td>
									<select name="throttler_mode">
										<option value="MESSAGES_PER_MINUTE">MESSAGES PER MINUTE</option>
										<option value="BYTES_PER_MINUTE">BYTES PER MINUTE</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="label">throttler amount</td>
								<td><input type="text" name="throttler_amount" value="" /></td>
							</tr>
							<tr>
								<td class="label">logger active</td>
								<td>
									<select name="logger_active">
										<option value="array">array</option>
										<option value="echo">echo</option>
									</select>
								</td>
							</tr>
							-->
							<tr>
								<td class="label">Bounce Interval (seconds)</td>
								<td><input type="text" name="bounce_interval" value="" /></td>
							</tr>
							<tr>
								<td class="label">Send Interval (seconds)</td>
								<td><input type="text" name="send_interval" value="" /></td>
							</tr>
							<tr>
								<td class="label">Send Limit</td>
								<td><input type="text" name="send_limit" value="" /></td>
							</tr>
						</table>


						<table class="sub">
							<caption>accounts rules</caption>
							<tr>
								<td class="label">accounts/month</td>
								<td><input type="text" name="ACCOUNTS_HOST" value="" /></td>
							</tr>
							<tr>
								<td class="label">ips/day</td>
								<td><input type="text" name="ACCOUNTS_IP_HOST" value="" /></td>
							</tr>
							<tr>
								<td class="label">accounts/ip</td>
								<td><input type="text" name="ACCOUNTS_IP" value="" /></td>
							</tr>
							<tr>
								<td class="label">accounts/interval</td>
								<td><input type="text" name="ACCOUNTS_INTERVAL" value="" /></td>
							</tr>
							<tr>
								<td class="label">mass_sender</td>
								<td>
									<select name="MASS_SENDER">
										<option value="0">inactive</option>
										<option value="1">active</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="label">whitelist mode</td>
								<td>
									<select name="WHITELIST">
										<option value="0">inactive</option>
										<option value="1">active</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="label">recycle dummy recipients </td>
								<td>
									<input type="text" name="RECIPIENTS_CYCLE" value="86400" />
								</td>
							</tr>
							<tr>
								<td class="label">ratio real/dummy recipients %</td>
								<td>
									<input type="text" name="RECIPIENTS_RATIO" value="40" />
								</td>
							</tr>
							<tr>
								<td class="label">accounts use proxy</td>
								<td>
									<select name="USE_PROXY">
										<option value="0">inactive</option>
										<option value="1">active</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="label">spam report type</td>
								<td>
									<select name="SPAM_REPORT_TYPE">
										<option value="0">smtp_api</option>
										<option value="1">rest_api</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="label">plugin name (if type is rest_api)</td>
								<td>
									<input type="text" name="SPAM_REPORT_PLUGIN" value="" />
								</td>
							</tr>
						</table>

					</td>
				</tr>
				<tr>
					<td colspan="2" class="control">
						<input type="hidden" name="host_id" value="" />
						<input type="hidden" name="task" value="add_server" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="button" class="button"  name="execute" value="next" />
					</td>
				</tr>

			</table>

		</form>
	</div>
</div>





<div class="dialog white_box">
	<div class="title">Mail Server Config</div>
	<div class="content">
		<form name="mailing" method="post" action="office/input">

			<table>
				<caption>Select one of the following sections.</caption>
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_preview');" value="Distribution preview" /></td>
				</tr>
				<!--
				<tr>
					<td class="center"><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_system_menu');" value="Distribution system" /></td>
				</tr>
				-->
				<tr>
					<td class="control">
						<input type="hidden" name="task" value="" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="hidden" name="execute" value="next" />
					</td>
				</tr>
			</table>

		</form>
	</div>
</div>




<div class="dialog white_box">
	<div class="title">Mail Server Config</div>
	<div class="content">
		<form name="mailing_preview" method="post" action="office/input">

			<table>
				<caption>Mailing preview.</caption>
				<tr>
					<td class="label">to:</td>
					<td><input type="text" name="email" /></td>
				</tr>
				<tr>
					<td class="label">subject:</td>
					<td><input type="text" name="subject" /></td>
				</tr>
				<tr>
					<td class="label">content:</td>
					<td>
						<textarea name="message"></textarea>
					</td>
				</tr>
				<tr>
					<td class="control" colspan="2">
						<input type="hidden" name="task" value="sendmail" />
						<input type="button" class="button"  name="back" value="return" />
						<input type="button" class="button"  name="execute" value="next" />
					</td>
				</tr>
			</table>

		</form>
	</div>
</div>



<div class="dialog white_box">
	<div>
		<form name="mailing_system_menu" method="post" action="office/input">
                    <div class="title">Distribution System Menu.</div>
                    <div  class="menu">
                        <div><input type="button" class="button"  name="cancel" value="Main Menu" /></div>
                        <div><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_system_campaigns');" value="campaigns" /></div>
                        <div><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_system_templates');" value="templates" /></div>
                        <div><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_system_emails');" value="emails" /></div>
                        <div><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_system_servers');" value="servers" /></div>
                    </div>
                    <div class="control"><input type="hidden" name="task" value="" /><input type="hidden" name="execute" value="next" /></div>
		</form>
	</div>
	
	
</div>
<div class="dialog white_box">
    <form name="mailing_system_recipients" method="post" action="office/input">
        <table>
                <tr>
                    <th colspan="2">Recipients</th>
                </tr>
                <tr>
                    <td><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_system_recipients',false,'add');" value="add" /></td>
                    <td><input type="button" class="button"  onclick="javascript:redirect(this,'mailing_system_recipients',false,'edit');" value="edit" /></td>
                </tr>
                <tr>
                    <td colspan="2" class="control" >
                        <input type="hidden" name="task" value="mailing_system_recipients" />
                        <input type="hidden" name="noundo" value="1" />
                        <input type="hidden" name="execute" value="next" />
                    </td>
                </tr>
        </table>
    </form>
</div>
<div class="dialog white_box">
    <form name="mailing_system_templates" method="post" action="office/input">
        <table>
                <tr>
                    <th>Templates</th>
                </tr>
                <tr>
                    <td class="control" >
                        <input type="hidden" name="task" value="mailing_system_templates" />
                        <input type="hidden" name="noundo" value="1" />
                        <input type="hidden" name="execute" value="next" />
                        <input type="button" class="button"  name="back" value="return" />
                    </td>
                </tr>
        </table>

    </form>
</div>
<div class="dialog white_box">
    <form name="mailing_system_campaigns" method="post" action="office/input">
        <table>
            <tr>
                <th>Campaigns</th>
            </tr>
            <tr>
                <td align="center">
                    <select name="content" multiple="true"></select>
                </td>
            </tr>
            <tr>
                <td class="control" >
                    <input type="hidden" name="task" value="mailing_system_campaigns_edit" />
                    <input type="hidden" name="noundo" value="1" />
                    
                    <input type="button" class="button"  name="back" value="return" />
                    <input type="button" class="button"  name="execute" value="next" />
                </td>
            </tr>
        </table>
    </form>
</div>
  <div class="dialog white_box">
    <form name="mailing_system_campaigns_edit" method="post" action="office/input">
        <div class="title">Campaings - edit mode.</div>
        <div style="padding:4px;">
            <table class="sub">
                <tr>
                    <td class="label">name</td>
                    <td><input type="text" name="name" value="" /></td>
                </tr>
                <tr>
                    <td class="label">description</td>
                    <td><input type="text" name="description" value="" /></td>
                </tr>
                <tr>
                    <td class="label">start</td>
                    <td><input type="text" name="start" value="" /></td>
                </tr>
                <tr>
                    <td class="label">end</td>
                    <td><input type="text" name="end" value="" /></td>
                </tr>
                <tr>
                    <td class="label">mode</td>
                    <td><select style="height:auto;" name="mode">
                        <option name="interval">interval</option>
                        <option name="date">date</option>
                    </select></td>
                </tr>
                <tr>
                    <td class="label">active</td>
                    <td><select style="height:auto;" name="active">
                        <option name="1">yes</option>
                        <option name="0">no</option>
                    </select></td>
                </tr>
            </table>    
        </div>
        <div class="control">
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="group_id" value="" />
            <input type="button" class="button"  name="back" value="cancel" />
            <input type="button" class="button"  name="execute" value="save" />
        </div>
    </form>
</div>