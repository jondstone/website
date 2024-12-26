<?php
	// Initialize variables
	$full_name = '';
	$email_address = '';
	$telephone_number = '';
	$your_message = '';
	$anti_spam = '';
	$form_load_time = 0;

	// Only proceed if the form is submitted via POST
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
		// Sanitize and assign POST data to variables, using empty string if not set
		$full_name = isset($_POST['Full_Name']) ? trim($_POST['Full_Name']) : '';
		$email_address = isset($_POST['Email_Address']) ? trim($_POST['Email_Address']) : '';
		$telephone_number = isset($_POST['Telephone_Number']) ? trim($_POST['Telephone_Number']) : '';
		$your_message = isset($_POST['Your_Message']) ? trim($_POST['Your_Message']) : '';
		$anti_spam = isset($_POST['AntiSpam']) ? $_POST['AntiSpam'] : '';
		$form_load_time = isset($_POST['formLoadTime']) ? $_POST['formLoadTime'] : 0;

		// Time-based check: Reject form if submitted too quickly (less than 5 seconds)
		$form_submit_time = time() * 1000; // Current timestamp in milliseconds
		$time_spent = ($form_submit_time - $form_load_time) / 1000; // Time spent in seconds

		// If time spent is too fast, kill the submission
		if ($time_spent < 5) {
			die('Form submission detected as bot activity. Please take your time.');
		}

		// Anti-spam check: if the extra_name field contains anything, kill the submission
		if (!empty($extra_name)) {
			die('Submission reject! You know why :)');
		}

		// Submit Form
		if (!empty($full_name) && !empty($email_address) && !empty($your_message) && $anti_spam === '12') {
			// Prepare email headers and body
			$headers = 'From: ' . $email_address . "\r\n" . 
					'Reply-To: ' . $email_address . "\r\n" . 
					'X-Mailer: PHP/' . phpversion();
			$to = 'info@jondstone.com';
			$subject = 'Inquiry';
			$body = "From: $full_name\nE-Mail: $email_address\nPhone: $telephone_number\nComments:\n$your_message";

			// Send the email
			if (mail($to, $subject, $body, $headers)) {
				echo '<p>Thank you, your inquiry has been received!</p>';
			} else {
				echo '<p>Something went wrong, please try again or send me an email directly!</p>';
			}
		} else {
			// If the anti-spam field is empty or incorrect, show a message to the user
			if (empty($anti_spam) || $anti_spam !== '12') {
				echo '<p>The challenge answer above is incorrect. Please try again.</p>';
			}
		}
	}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
	<!-- Header content removed as it's not necessary to be displayed in Git.-->
</head>

<body>
	<script src="formvalidation.js"></script>
	<script>
		required.add('Full_Name','NOT_EMPTY','Full Name');
		required.add('Email_Address','EMAIL','Email Address');
		required.add('Your_Message','NOT_EMPTY','Your Message');
		required.add('AntiSpam','NOT_EMPTY','Anti-Spam Question');
	</script>
	<noscript>
        Javascript is currently disabled. This site requires Javascript to be enabled.
    </noscript>

	<div id="content">
		<br />
		<div id="contactPageLayout">
			<div id="contactText">
				<!-- Contact Page content removed as it's not necessary to be displayed in Git.-->
			</div>
			<div id="contactForm">
				<form method="post" action="index.php" name="contactform" onsubmit="return validate.check(this)">
					<input type="hidden" name="formLoadTime" value="<?php echo time() * 1000; ?>">
					<input type="text" name="extra_name" id="extra_name" style="display:none;">

					<table class="contactform">
						<tr>
							<td colspan="2" style="padding-bottom:10px">
								<div class="contactformmessage">Fields marked with a <span class="required_star">*</span> are required.</div>
							</td>
						</tr>
						<tr>
							<td valign="top">
								<label for="Full_Name" class="required">Full Name: <span class="required_star"> * </span></label>
							</td>
							<td valign="top">
								<input type="text" name="Full_Name" id="Full_Name" maxlength="50" style="width:230px">
							</td>
						</tr>
						<tr>
							<td valign="top">
								<label for="Email_Address" class="required">Email Address: <span class="required_star"> * </span></label>
							</td>
							<td valign="top">
								<input type="text" name="Email_Address" id="Email_Address" maxlength="50" style="width:230px">
							</td>
						</tr>
						<tr>
							<td valign="top">
								<label for="Telephone_Number" class="not-required">Telephone:</label>
							</td>
							<td valign="top">
								<input type="text" name="Telephone_Number" id="Telephone_Number" maxlength="20" style="width:230px">
							</td>
						</tr>
						<tr>
							<td valign="top">
								<label for="Comments" class="required">Comments: <span class="required_star"> * </span></label>
							</td>
							<td valign="top">
								<textarea style="width:230px;height:160px" name="Your_Message" id="Your_Message" maxlength="2000"></textarea>
							</td>
						</tr>
						<tr>
							<td>
								<label for="AntiSpam" class="required">5 plus 7 is? <span class="required_star"> * </span>&nbsp;</label>
							</td>
							<td valign="top">
								<input type="text" name="AntiSpam" id="AntiSpam" maxlength="2" style="width:25px">
							</td>
						</tr>
						<tr>
							<td valign="top">
								<input type="submit" name="submit" value="Submit Inquiry" style="width:125px;height:25px">
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</div>
	
</body>
</html>