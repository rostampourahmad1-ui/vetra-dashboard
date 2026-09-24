<?php
/**
 * Persian strings for the dashboard front-end and asynchronous responses.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_I18n {

	protected static $translations = null;

	public static function init() {
		add_filter( 'gettext', array( __CLASS__, 'translate' ), 15, 3 );
		add_filter( 'gettext_with_context', array( __CLASS__, 'translate_with_context' ), 15, 4 );
	}

	public static function translate_with_context( $translation, $text, $context, $domain ) {
		if ( 'vetra-dashboard' === $domain && 'time ago' === $context && '%s ago' === $text ) {
			return '%s پیش';
		}
		return $translation;
	}

	public static function translate( $translation, $text, $domain ) {
		if ( 'vetra-dashboard' !== $domain ) {
			return $translation;
		}
		if ( null === self::$translations ) {
			self::$translations = array(
			// Navigation and shared controls.
			'Dashboard' => 'داشبورد', 'Profile' => 'پروفایل', 'Support Requests' => 'درخواست‌های پشتیبانی',
			'New Ticket' => 'ثبت تیکت جدید', 'Ticket' => 'تیکت', 'Support Center' => 'مرکز پشتیبانی',
			'Notifications' => 'اعلان‌ها', 'Polls' => 'نظرسنجی‌ها', 'Attachments' => 'پیوست‌ها',
			'My Wallet' => 'کیف پول', 'Wallet' => 'کیف پول', 'Bank Information' => 'اطلاعات بانکی', 'Comments' => 'دیدگاه‌ها',
			'Logout' => 'خروج از حساب', 'Menu' => 'منو', 'Theme' => 'پوسته', 'Mark all read' => 'علامت‌گذاری همه به‌عنوان خوانده‌شده',
			'Loading...' => 'در حال بارگذاری…', 'Loading' => 'در حال بارگذاری', 'View' => 'مشاهده', 'Close' => 'بستن',
			'Save' => 'ذخیره', 'Save changes' => 'ذخیره تغییرات', 'Submit' => 'ثبت', 'Cancel' => 'انصراف',
			'Filter' => 'فیلتر', 'All' => 'همه', 'Select' => 'انتخاب کنید', 'Download' => 'دریافت فایل',
			'Pending' => 'در انتظار بررسی', 'Approved' => 'تأییدشده', 'Rejected' => 'ردشده', 'Paid' => 'پرداخت‌شده', 'Open' => 'باز',
			'Investigating' => 'در حال بررسی', 'Answered' => 'پاسخ داده‌شده', 'Awaiting Reply' => 'در انتظار پاسخ',
			'Closed' => 'بسته', 'Low' => 'کم', 'Medium' => 'متوسط', 'High' => 'زیاد', 'General' => 'عمومی',
			'Single choice' => 'تک‌گزینه‌ای', 'Multiple choice' => 'چندگزینه‌ای', 'Credit' => 'افزایش موجودی', 'Debit' => 'کاهش موجودی',
			'Are you sure?' => 'آیا مطمئن هستید؟', 'Something went wrong' => 'مشکلی پیش آمد.', 'Saved successfully' => 'با موفقیت ذخیره شد.',
			'Copied' => 'کپی شد', 'No data found.' => 'اطلاعاتی یافت نشد.',

			// Sign-in, sign-up, and password recovery.
			'Sign in to your account' => 'ورود به حساب کاربری', 'Welcome back. Please enter your details.' => 'خوش آمدید؛ اطلاعات ورود خود را وارد کنید.',
			'Sign in to access your dashboard' => 'برای دسترسی به داشبورد وارد شوید', 'Please log in or create an account to continue.' => 'برای ادامه وارد شوید یا حساب کاربری بسازید.',
			'Verification code' => 'کد تأیید', 'Confirm' => 'تأیید', 'Resend code' => 'ارسال دوبارهٔ کد',
			'Password' => 'گذرواژه', 'SMS code' => 'کد پیامکی', 'Email, username or phone' => 'ایمیل، نام کاربری یا شماره موبایل',
			'Email, username or mobile' => 'ایمیل، نام کاربری یا شماره موبایل', 'Remember me' => 'مرا به خاطر بسپار',
			'Forgot password?' => 'گذرواژه را فراموش کرده‌اید؟', 'Sign in' => 'ورود', 'Mobile number' => 'شماره موبایل',
			'Send code' => 'ارسال کد', 'Do not have an account?' => 'حساب کاربری ندارید؟', 'Create one' => 'ثبت‌نام کنید',
			'Create your account' => 'ساخت حساب کاربری', 'Join us in a few seconds.' => 'در چند ثانیه به ما بپیوندید.',
			'First name' => 'نام', 'Last name' => 'نام خانوادگی', 'Username' => 'نام کاربری', 'Email' => 'ایمیل',
			'I accept the rules.' => 'قوانین را می‌پذیرم.', 'Create account' => 'ساخت حساب کاربری', 'Already registered?' => 'قبلاً ثبت‌نام کرده‌اید؟',
			'Reset your password' => 'بازیابی گذرواژه', 'We will send a reset code to your mobile or email.' => 'کد بازیابی به موبایل یا ایمیل شما ارسال می‌شود.',
			'New password' => 'گذرواژهٔ جدید', 'Confirm new password' => 'تکرار گذرواژهٔ جدید', 'Change password' => 'تغییر گذرواژه',
			'Send reset code' => 'ارسال کد بازیابی', 'Back to sign in' => 'بازگشت به ورود', 'You are already logged in.' => 'هم‌اکنون وارد حساب خود هستید.',
			'Go to dashboard' => 'رفتن به داشبورد', 'Registration is currently disabled.' => 'ثبت‌نام در حال حاضر غیرفعال است.',
			'Your account is disabled.' => 'حساب کاربری شما غیرفعال شده است.', 'Security check failed. Please try again.' => 'بررسی امنیتی ناموفق بود؛ دوباره تلاش کنید.',
			'Password login is disabled.' => 'ورود با گذرواژه غیرفعال است.', 'Please enter your credentials.' => 'اطلاعات ورود را وارد کنید.',
			'Captcha verification failed.' => 'تأیید کپچا ناموفق بود.', 'The username or password is incorrect.' => 'نام کاربری یا گذرواژه نادرست است.',
			'OTP login is disabled.' => 'ورود پیامکی غیرفعال است.', 'The mobile number is incorrect.' => 'شماره موبایل معتبر نیست.',
			'There is no user with this mobile number.' => 'کاربری با این شماره موبایل پیدا نشد.', 'The verification code was sent.' => 'کد تأیید ارسال شد.',
			'User not found.' => 'کاربر پیدا نشد.', 'Please enter a valid username.' => 'نام کاربری معتبر وارد کنید.',
			'Please enter a valid email.' => 'ایمیل معتبر وارد کنید.', 'This email is already registered.' => 'این ایمیل قبلاً ثبت شده است.',
			'This number is already in use.' => 'این شماره قبلاً استفاده شده است.', 'Password must be at least 8 characters long.' => 'گذرواژه باید دست‌کم ۸ نویسه باشد.',
			'Please confirm the rules.' => 'پذیرش قوانین را تأیید کنید.', 'Your account was created successfully.' => 'حساب کاربری شما با موفقیت ساخته شد.',
			'Registration completed successfully. Please log in.' => 'ثبت‌نام با موفقیت انجام شد؛ وارد حساب شوید.',
			'No user found with the provided information.' => 'کاربری با اطلاعات واردشده پیدا نشد.',
			'The verification code was sent to your mobile number.' => 'کد تأیید به شماره موبایل شما ارسال شد.',
			'Password reset' => 'بازیابی گذرواژه', 'Reset your password using this link: %s' => 'برای بازیابی گذرواژه از این پیوند استفاده کنید: %s',
			'The reset link was sent to your email.' => 'پیوند بازیابی به ایمیل شما ارسال شد.', 'Passwords do not match.' => 'گذرواژه‌ها با هم مطابقت ندارند.',
			'Your link has expired. Please try again.' => 'پیوند شما منقضی شده است؛ دوباره درخواست دهید.',
			'Your password has been changed successfully.' => 'گذرواژه با موفقیت تغییر کرد.',

			// Dashboard overview and profile.
			'Open tickets' => 'تیکت‌های باز', 'Unread notifications' => 'اعلان‌های خوانده‌نشده', 'Wallet balance' => 'موجودی کیف پول',
			'Total tickets' => 'مجموع تیکت‌ها', 'Hi %s, welcome back' => 'سلام %s، خوش آمدید',
			'Here is a quick overview of your account.' => 'خلاصه‌ای از وضعیت حساب شما', 'New ticket' => 'تیکت جدید',
			'Quick access' => 'دسترسی سریع', 'Account summary' => 'خلاصهٔ حساب', 'Active polls' => 'نظرسنجی‌های فعال',
			'Phone verified' => 'شماره موبایل تأیید شده', 'Phone not verified' => 'شماره موبایل تأیید نشده',
			'Email verified' => 'ایمیل تأیید شده', 'Email not verified' => 'ایمیل تأیید نشده', 'Personal details' => 'اطلاعات شخصی',
			'Mobile' => 'موبایل', 'Gender' => 'جنسیت', 'Male' => 'مرد', 'Female' => 'زن', 'About' => 'دربارهٔ من',
			'Change password' => 'تغییر گذرواژه', 'Current password' => 'گذرواژهٔ فعلی', 'Update password' => 'به‌روزرسانی گذرواژه',
			'My attachments' => 'پیوست‌های من', 'No attachments found.' => 'پیوستی پیدا نشد.', 'No comments found.' => 'دیدگاهی پیدا نشد.',
			'On:' => 'در:', 'Approved' => 'تأییدشده', 'Pending' => 'در انتظار بررسی',

			// Tickets, messages, and support.
			'Submit a new ticket' => 'ثبت تیکت جدید', 'Subject' => 'موضوع', 'Department' => 'دپارتمان', 'Select department' => 'انتخاب دپارتمان',
			'Priority' => 'اولویت', 'Message' => 'پیام', 'Submit ticket' => 'ثبت تیکت', 'No tickets found.' => 'تیکتی پیدا نشد.',
			'Updated' => 'آخرین به‌روزرسانی', 'Star' => 'نشان‌دار کردن', 'Close ticket' => 'بستن تیکت', 'Support' => 'پشتیبانی',
			'Internal note' => 'یادداشت داخلی', 'Reply' => 'پاسخ', 'Write your reply...' => 'پاسخ خود را بنویسید…',
			'Send reply' => 'ارسال پاسخ', 'How was the support?' => 'از پشتیبانی راضی بودید؟', 'Your feedback...' => 'نظر خود را بنویسید…',
			'Submit rating' => 'ثبت امتیاز', 'All departments' => 'همهٔ دپارتمان‌ها', 'All statuses' => 'همهٔ وضعیت‌ها',
			'Ticket ID or title' => 'شناسه یا عنوان تیکت', 'No notifications yet.' => 'هنوز اعلانی ندارید.',
			'No polls available.' => 'نظرسنجی‌ای موجود نیست.', '%d participants' => '%d شرکت‌کننده',
			'Submit vote' => 'ثبت رأی', 'You have voted' => 'شما رأی داده‌اید',

			// Banking and wallet.
			'Add bank card' => 'افزودن کارت بانکی', 'Bank' => 'بانک', 'Select bank' => 'انتخاب بانک', 'Card owner' => 'نام صاحب کارت',
			'Card number' => 'شماره کارت', 'Sheba number' => 'شماره شبا', 'No bank cards yet.' => 'هنوز کارتی ثبت نشده است.',
			'Available balance' => 'موجودی قابل برداشت', 'Request withdrawal' => 'درخواست برداشت', 'Add an approved bank card first.' => 'ابتدا یک کارت بانکی تأییدشده اضافه کنید.',
			'Amount' => 'مبلغ', 'Bank card' => 'کارت بانکی', 'Note' => 'یادداشت', 'Submit request' => 'ثبت درخواست',
			'Withdrawal requests' => 'درخواست‌های برداشت', 'No requests yet.' => 'هنوز درخواستی ثبت نشده است.',
			'Recent transactions' => 'تراکنش‌های اخیر', 'No transactions found.' => 'تراکنشی پیدا نشد.',
			'Details' => 'جزئیات', 'Type' => 'نوع', 'Balance' => 'موجودی', 'Date' => 'تاریخ',
			'Banking is disabled.' => 'مدیریت کارت بانکی غیرفعال است.', 'Please select a bank.' => 'یک بانک انتخاب کنید.',
			'Please enter the card owner.' => 'نام صاحب کارت را وارد کنید.', 'Please enter a valid card number.' => 'شماره کارت معتبر وارد کنید.',
			'Please enter a valid Sheba number.' => 'شماره شبای معتبر وارد کنید.', 'You do not have access.' => 'اجازهٔ دسترسی ندارید.',
			'Insufficient balance.' => 'موجودی کافی نیست.', 'Wallet is disabled.' => 'کیف پول غیرفعال است.',
			'Minimum withdrawal amount is %s.' => 'حداقل مبلغ برداشت %s است.', 'Please select a valid bank card.' => 'کارت بانکی معتبر انتخاب کنید.',
			'The selected bank card is not approved yet.' => 'کارت بانکی انتخاب‌شده هنوز تأیید نشده است.',
			'You already have a pending withdrawal request.' => 'یک درخواست برداشت در حال بررسی دارید.',
			'Request not found.' => 'درخواست پیدا نشد.', 'Invalid status.' => 'وضعیت نامعتبر است.', 'Withdrawal' => 'برداشت',
			'Wallet balance updated' => 'موجودی کیف پول به‌روزرسانی شد', 'A %1$s transaction was recorded. New balance: %2$s' => 'تراکنش %1$s ثبت شد. موجودی جدید: %2$s',

			// Notifications, attachments, polls, and comments.
			'No poll found.' => 'نظرسنجی پیدا نشد.', 'Invalid poll.' => 'نظرسنجی نامعتبر است.',
			'You have already voted on this poll.' => 'شما قبلاً در این نظرسنجی رأی داده‌اید.', 'Please choose an option.' => 'یک گزینه انتخاب کنید.',
			'No option selected.' => 'گزینه‌ای انتخاب نشده است.', 'The operation was successful.' => 'عملیات با موفقیت انجام شد.',
			'The operation failed.' => 'عملیات ناموفق بود.', 'Ticket registered' => 'تیکت ثبت شد',
			'Your ticket #%d has been registered.' => 'تیکت شمارهٔ %d شما ثبت شد.', 'New reply from support' => 'پاسخ تازه از پشتیبانی',
			'A new reply was posted on ticket #%d.' => 'پاسخ تازه‌ای برای تیکت شمارهٔ %d ثبت شد.',
			'Welcome to Vetra' => 'به وترا خوش آمدید', 'Your account is ready. Complete your profile and explore the dashboard.' => 'حساب شما آماده است؛ پروفایل خود را تکمیل و داشبورد را بررسی کنید.',

			// REST responses and verification messages.
			'The mobile number is incorrect.' => 'شماره موبایل معتبر نیست.', 'Please wait before requesting a new code.' => 'برای درخواست کد تازه کمی صبر کنید.',
			'SMS service is disabled.' => 'سرویس پیامک غیرفعال است.', 'Verified successfully.' => 'با موفقیت تأیید شد.', 'Done.' => 'انجام شد.',
			'Profile updated successfully.' => 'پروفایل با موفقیت به‌روزرسانی شد.', 'Your password has been changed.' => 'گذرواژهٔ شما تغییر کرد.',
			'Avatar updated.' => 'تصویر پروفایل به‌روزرسانی شد.', 'Ticket submitted successfully.' => 'تیکت با موفقیت ثبت شد.',
			'Your reply has been sent.' => 'پاسخ شما ارسال شد.', 'Ticket closed.' => 'تیکت بسته شد.',
			'Your feedback has been submitted.' => 'نظر شما ثبت شد.', 'Your vote has been recorded.' => 'رأی شما ثبت شد.',
			'Bank card added.' => 'کارت بانکی اضافه شد.', 'The deletion was successful.' => 'حذف با موفقیت انجام شد.',
			'Withdrawal request submitted.' => 'درخواست برداشت ثبت شد.', 'Editing is disabled.' => 'ویرایش غیرفعال است.',
			'This email is used by another user.' => 'این ایمیل توسط کاربر دیگری استفاده شده است.', '%s is required.' => 'وارد کردن «%s» الزامی است.',
			'User not found.' => 'کاربر پیدا نشد.', 'Your old password is incorrect.' => 'گذرواژهٔ فعلی نادرست است.',
			'Avatar upload is disabled.' => 'بارگذاری تصویر پروفایل غیرفعال است.', 'No file uploaded.' => 'فایلی بارگذاری نشده است.',
			'The maximum upload size is 2MB.' => 'حداکثر حجم فایل ۲ مگابایت است.', 'The verification code was sent to your email.' => 'کد تأیید به ایمیل شما ارسال شد.',
			'Your mobile number was verified.' => 'شماره موبایل شما تأیید شد.', 'Your email was verified.' => 'ایمیل شما تأیید شد.',
			'The verification code is incorrect.' => 'کد تأیید نادرست است.', 'Invalid field.' => 'فیلد نامعتبر است.',
			'No verification code found.' => 'کد تأییدی پیدا نشد.', 'The verification code has expired.' => 'کد تأیید منقضی شده است.',
			'Too many attempts. Request a new code.' => 'تلاش‌های زیادی انجام شده؛ کد تازه‌ای درخواست کنید.',
			'Your verification code is: %s' => 'کد تأیید شما: %s', 'Your verification code is %s' => 'کد تأیید شما %s است.',
			'Please enter a valid Jalali birthday.' => 'لطفاً تاریخ تولد شمسی معتبر وارد کنید.',
			'Please enter a valid Jalali date for %s.' => 'لطفاً تاریخ شمسی معتبر برای «%s» وارد کنید.',
			'Your account was created successfully.' => 'حساب کاربری شما با موفقیت ساخته شد.',
			'No attachments found.' => 'پیوستی پیدا نشد.', 'No comments found.' => 'دیدگاهی پیدا نشد.',
			'File not found.' => 'فایل پیدا نشد.', 'You do not have access to this file.' => 'اجازهٔ دسترسی به این فایل را ندارید.',
			'The ticket system is disabled.' => 'سامانهٔ تیکت غیرفعال است.', 'The ticket title is required.' => 'وارد کردن عنوان تیکت الزامی است.',
			'The ticket content is required.' => 'وارد کردن متن تیکت الزامی است.', 'The ticket department is required.' => 'انتخاب دپارتمان الزامی است.',
			'You have reached the maximum number of open tickets.' => 'به حداکثر تعداد تیکت‌های باز رسیده‌اید.',
			'You do not have access to this ticket.' => 'به این تیکت دسترسی ندارید.', 'This ticket is closed.' => 'این تیکت بسته شده است.',
			'The reply content is required.' => 'وارد کردن متن پاسخ الزامی است.', 'Invalid status.' => 'وضعیت نامعتبر است.',
			'Ticket not found.' => 'تیکت پیدا نشد.', 'You do not have access to this page.' => 'به این صفحه دسترسی ندارید.',
			'No verification code found.' => 'کد تأییدی پیدا نشد.', 'Email verification' => 'تأیید ایمیل',
			'Vetra Dashboard test message.' => 'پیام آزمایشی پیشخوان وترا.',
			'Ticket attachment' => 'پیوست تیکت', 'User avatar' => 'تصویر پروفایل', 'Comments' => 'دیدگاه‌ها',
			'Please enter a valid Sheba number.' => 'شماره شبای معتبر وارد کنید.', 'The ticket reply is required.' => 'متن پاسخ تیکت را وارد کنید.',
			'No tickets found.' => 'تیکتی پیدا نشد.', 'No bank cards yet.' => 'هنوز کارتی ثبت نشده است.',
			'The ticket title is required.' => 'وارد کردن عنوان تیکت الزامی است.', 'The ticket content is required.' => 'وارد کردن متن تیکت الزامی است.',
			'The ticket department is required.' => 'انتخاب دپارتمان الزامی است.', 'You have reached the maximum number of open tickets.' => 'به حداکثر تعداد تیکت‌های باز رسیده‌اید.',
			'The reply content is required.' => 'وارد کردن متن پاسخ الزامی است.', 'Ticket attachment' => 'پیوست تیکت',
			'Login' => 'ورود', 'Signup' => 'ثبت‌نام', 'Phone' => 'تلفن همراه', 'User' => 'کاربر', 'Target' => 'محدوده دسترسی',
			'No tickets found.' => 'تیکتی پیدا نشد.', 'No bank cards yet.' => 'هنوز کارتی ثبت نشده است.',
			'No polls available.' => 'نظرسنجی‌ای موجود نیست.', 'No notifications yet.' => 'هنوز اعلانی ندارید.',
			'Select page' => 'انتخاب برگه', 'This dashboard page is unavailable.' => 'این صفحهٔ داشبورد در دسترس نیست.',
			'You do not have access to this section.' => 'به این بخش دسترسی ندارید.',
			'IPPanel API key is missing.' => 'کلید API آی‌پی‌پنل وارد نشده است.', 'Sender number is missing.' => 'شمارهٔ فرستنده وارد نشده است.',
			'IPPanel rejected the message.' => 'آی‌پی‌پنل پیام را نپذیرفت.', 'IPPanel request failed.' => 'درخواست به آی‌پی‌پنل ناموفق بود.',
			'Webhook URL is missing.' => 'نشانی وب‌هوک وارد نشده است.', 'Webhook request failed.' => 'درخواست وب‌هوک ناموفق بود.',
			'Custom webhook' => 'وب‌هوک سفارشی', 'IPPanel (edge.ippanel.com)' => 'آی‌پی‌پنل (edge.ippanel.com)',
			'Invalid phone number.' => 'شمارهٔ موبایل نامعتبر است.', 'Your account has been created successfully.' => 'حساب کاربری شما با موفقیت ساخته شد.',
			'This email is used by another user.' => 'این ایمیل توسط کاربر دیگری استفاده شده است.',
			'You do not have access to this file.' => 'به این فایل دسترسی ندارید.',
			'Email verification' => 'تأیید ایمیل', 'Your verification code is: %s' => 'کد تأیید شما: %s',
			'This email is already registered.' => 'این ایمیل قبلاً ثبت شده است.',
			'Wallet balance updated' => 'موجودی کیف پول به‌روزرسانی شد',
			'This email was sent by %s.' => 'این ایمیل از طرف %s ارسال شده است.',
			'Email template not found.' => 'قالب ایمیل پیدا نشد.', 'Permission denied.' => 'اجازهٔ دسترسی ندارید.',
			'Hi %s, welcome to our community. Your account has been created successfully.' => 'سلام %s، به جمع ما خوش آمدید. حساب شما با موفقیت ساخته شد.',
			'Welcome' => 'خوش آمدید', 'Your account is ready' => 'حساب شما آماده است',
			'A new ticket was registered by %s.' => 'تیکت تازه‌ای توسط %s ثبت شد.', 'View ticket' => 'مشاهدهٔ تیکت',
			'New ticket #%d' => 'تیکت تازهٔ شمارهٔ %d', 'New support ticket' => 'تیکت تازهٔ پشتیبانی',
			'New reply from user' => 'پاسخ تازه از کاربر', 'Ticket #%d received a new reply.' => 'برای تیکت شمارهٔ %d پاسخ تازه‌ای ثبت شد.',
			'View conversation' => 'مشاهدهٔ گفتگو', 'Your bank card was approved.' => 'کارت بانکی شما تأیید شد.',
			'Your bank card was rejected.' => 'کارت بانکی شما رد شد.', 'Your bank card is under review.' => 'کارت بانکی شما در حال بررسی است.',
			'Your bank card status changed.' => 'وضعیت کارت بانکی شما تغییر کرد.', 'Bank card status' => 'وضعیت کارت بانکی',
			'Your withdrawal request was approved.' => 'درخواست برداشت شما تأیید شد.', 'Your withdrawal request was rejected.' => 'درخواست برداشت شما رد شد.',
			'Your withdrawal request has been paid.' => 'مبلغ درخواست برداشت شما پرداخت شد.', 'Your withdrawal status changed.' => 'وضعیت درخواست برداشت شما تغییر کرد.',
			'Withdrawal status' => 'وضعیت برداشت', 'This email was sent by %s.' => 'این ایمیل از طرف %s ارسال شده است.',
			);
		}
		return isset( self::$translations[ $text ] ) ? self::$translations[ $text ] : $translation;
	}
}
