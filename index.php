<?php
// --- PHP Logic for Processing & Routing ---
$is_submitted = false;
$fullName = $email = $programName = $appId = $phone = $bio = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $fullName = sanitize_input($_POST['fullName'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $program = sanitize_input($_POST['program'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $bio = sanitize_input($_POST['bio'] ?? '');

    // Server-side validation
    if (empty($fullName)) {
        $errors['fullName'] = "Full Name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $fullName)) {
        $errors['fullName'] = "Name should only contain letters and spaces.";
    }

    if (empty($email)) {
        $errors['email'] = "Email Address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    }

    if (empty($program)) {
        $errors['program'] = "Please select a program.";
    }

    if (empty($phone)) {
        $errors['phone'] = "Phone Number is required.";
    } else {
        // Accepts international + and digits, or 10 digit local format
        $normalizedPhone = preg_replace('/[^\d+]/', '', $phone);
        if (!preg_match('/^\+?\d{7,15}$/', $normalizedPhone)) {
            $errors['phone'] = "Please enter a valid phone number (7–15 digits, optional +).";
        } else {
            $phone = $normalizedPhone;
        }
    }

    if (!empty($bio) && mb_strlen($bio) > 500) {
        $errors['bio'] = "Bio must be 500 characters or fewer.";
    }

    // If no errors, mark as submitted
    if (empty($errors)) {
        $is_submitted = true;
        $programMap = ['CS' => 'Computer Science', 'IT' => 'Information Technology', 'EC' => 'Electronics & Communication'];
        $programName = $programMap[$program] ?? "Unknown Program";
        $appId = "SAP-" . rand(10000, 99999);
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo $is_submitted ? "Submission Success — Sapthagiri" : "Sapthagiri Registration Form"; ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background-color: #f0f4f8; }
        .error-message { color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; }
        .success-card { animation: fadeInUp 0.45s ease-out; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        /* subtle focus for accessibility */
        .focus-ring:focus { outline: none; box-shadow: 0 0 0 4px rgba(99,102,241,0.12); border-color: #6366f1; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

<?php if ($is_submitted): // Display SUCCESS page ?>

    <div class="w-full max-w-xl bg-white p-6 md:p-10 rounded-xl shadow-2xl border-4 border-indigo-400 success-card">
        <div class="text-center mb-6">
            <span class="text-6xl mb-2 block animate-bounce">🎉</span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-indigo-700 mt-2">Woohoo! Application Submitted</h1>
            <p class="text-gray-600 mt-2">Thank you for registering with Sapthagiri. We've received your details.</p>
        </div>

        <div class="bg-indigo-50 p-4 rounded-lg mb-4 border-l-4 border-indigo-500">
            <p class="text-sm font-semibold text-indigo-700">Your Reference ID</p>
            <p class="text-2xl font-black text-indigo-900 mt-1"><?php echo htmlspecialchars($appId); ?></p>
        </div>

        <h3 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Summary of Your Registration</h3>

        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-600">Name</div>
                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($fullName); ?></div>

                <div class="text-sm text-gray-600">Email</div>
                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($email); ?></div>

                <div class="text-sm text-gray-600">Phone</div>
                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($phone); ?></div>

                <div class="text-sm text-gray-600">Program</div>
                <div class="font-bold text-green-700"><?php echo htmlspecialchars($programName); ?></div>

                <div class="text-sm text-gray-600">Bio</div>
                <div class="text-gray-800"><?php echo nl2br(htmlspecialchars($bio ?: '—')); ?></div>
            </div>
        </div>

        <div class="mt-6 flex flex-col md:flex-row gap-3 justify-center">
             <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="inline-block py-2 px-6 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition duration-150">Register Another</a>
             <a href="#" onclick="window.print();" class="inline-block py-2 px-6 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition duration-150">Print / Save</a>
        </div>
    </div>

<?php else: // Display REGISTRATION FORM ?>

    <div class="w-full max-w-5xl grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- FORM CARD -->
        <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg border border-gray-100">
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-2 text-center">Sapthagiri Registration Form</h1>
            <p class="text-gray-500 mb-6 text-center">Fill the form to register for your selected program.</p>

            <form id="registrationForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-5" novalidate>

                <!-- Full Name -->
                <div>
                    <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" id="fullName" name="fullName" required
                           value="<?php echo htmlspecialchars($fullName); ?>"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                           placeholder="e.g., John Doe" aria-describedby="fullNameError">
                    <div id="fullNameError" class="error-message"><?php echo $errors['fullName'] ?? ''; ?></div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($email); ?>"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                           placeholder="you@example.com" aria-describedby="emailError">
                    <div id="emailError" class="error-message"><?php echo $errors['email'] ?? ''; ?></div>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required
                           value="<?php echo htmlspecialchars($phone); ?>"
                           class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                           placeholder="+91 9876543210" aria-describedby="phoneError" inputmode="tel">
                    <div id="phoneError" class="error-message"><?php echo $errors['phone'] ?? ''; ?></div>
                </div>

                <!-- Program Selection -->
                <div>
                    <label for="program" class="block text-sm font-medium text-gray-700">Program Applied For</label>
                    <select id="program" name="program" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                            aria-describedby="programError">
                        <option value="">Select a Program</option>
                        <option value="CS" <?php echo (isset($_POST['program']) && $_POST['program'] === 'CS') ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="IT" <?php echo (isset($_POST['program']) && $_POST['program'] === 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                        <option value="EC" <?php echo (isset($_POST['program']) && $_POST['program'] === 'EC') ? 'selected' : ''; ?>>Electronics & Communication</option>
                    </select>
                    <div id="programError" class="error-message"><?php echo $errors['program'] ?? ''; ?></div>
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700">Short Bio <span class="text-xs text-gray-400">(optional, max 500 chars)</span></label>
                    <textarea id="bio" name="bio" rows="4" maxlength="500"
                              class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                              placeholder="Tell us a bit about yourself..."><?php echo htmlspecialchars($bio); ?></textarea>
                    <div class="flex justify-between items-center mt-1">
                        <div id="bioError" class="error-message"><?php echo $errors['bio'] ?? ''; ?></div>
                        <div class="text-sm text-gray-500"><span id="bioCount"><?php echo mb_strlen($bio); ?></span>/500</div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-lg text-lg font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        Submit Application
                    </button>
                </div>
            </form>

            <!-- Inline small note -->
            <p class="mt-4 text-xs text-gray-500 text-center">You can preview your entry in real-time on the right. All data is validated before submit.</p>
        </div>

        <!-- LIVE PREVIEW (responsive) -->
        <div class="bg-white p-6 md:p-8 rounded-xl shadow-lg border border-gray-100 h-fit">
            <h2 class="text-lg font-bold text-gray-700 mb-4">Live Preview</h2>

            <div id="previewCard" class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-xl font-bold">S</div>
                    <div>
                        <div id="previewName" class="text-base font-semibold text-gray-900"><?php echo htmlspecialchars($fullName ?: 'Your Name'); ?></div>
                        <div id="previewProgram" class="text-sm text-gray-600"><?php echo htmlspecialchars($programName ?: 'Program (preview)'); ?></div>
                    </div>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-600">Email</div>
                    <div id="previewEmail" class="font-medium text-gray-800"><?php echo htmlspecialchars($email ?: 'you@example.com'); ?></div>
                </div>

                <div class="p-3 bg-gray-50 rounded-lg">
                    <div class="text-sm text-gray-600">Phone</div>
                    <div id="previewPhone" class="font-medium text-gray-800"><?php echo htmlspecialchars($phone ?: '—'); ?></div>
                </div>

                <div class="p-3 bg-green-50 rounded-lg">
                    <div class="text-sm text-gray-600">Bio</div>
                    <div id="previewBio" class="text-gray-800"><?php echo htmlspecialchars($bio ?: 'A short bio will appear here.'); ?></div>
                </div>

                <div class="mt-3 text-right">
                    <div class="text-xs text-gray-400">Preview doesn't submit data — it's just for display.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript: client-side validation + live preview -->
    <script>
        $(document).ready(function() {
            // update bio counter
            function updateBioCount() {
                $('#bioCount').text($('#bio').val().length);
            }
            updateBioCount();

            // Live preview updates
            function updatePreview() {
                const name = $('#fullName').val().trim() || 'Your Name';
                const email = $('#email').val().trim() || 'you@example.com';
                const phone = $('#phone').val().trim() || '—';
                const bio = $('#bio').val().trim() || 'A short bio will appear here.';
                const programText = $('#program option:selected').text() || 'Program (preview)';

                $('#previewName').text(name);
                $('#previewEmail').text(email);
                $('#previewPhone').text(phone);
                $('#previewBio').text(bio);
                $('#previewProgram').text(programText);
            }

            $('#fullName, #email, #phone, #bio, #program').on('input change', function() {
                updateBioCount();
                updatePreview();
                $('.error-message').text('');
            });

            // client-side validation before submit
            $('#registrationForm').on('submit', function(e) {
                let isValid = true;
                $('.error-message').text('');

                const fullName = $('#fullName').val().trim();
                const nameRegex = /^[a-zA-Z\s]+$/;
                if (!fullName) {
                    $('#fullNameError').text('Full Name is required.');
                    isValid = false;
                } else if (!nameRegex.test(fullName)) {
                    $('#fullNameError').text('Name should only contain letters and spaces.');
                    isValid = false;
                }

                const email = $('#email').val().trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email) {
                    $('#emailError').text('Email Address is required.');
                    isValid = false;
                } else if (!emailRegex.test(email)) {
                    $('#emailError').text('Please enter a valid email address.');
                    isValid = false;
                }

                const phone = $('#phone').val().trim();
                const phoneNormalized = phone.replace(/[^\d+]/g,'');
                const phoneRegex = /^\+?\d{7,15}$/;
                if (!phone) {
                    $('#phoneError').text('Phone Number is required.');
                    isValid = false;
                } else if (!phoneRegex.test(phoneNormalized)) {
                    $('#phoneError').text('Please enter a valid phone number (7–15 digits, optional +).');
                    isValid = false;
                }

                const program = $('#program').val();
                if (!program) {
                    $('#programError').text('Please select a program.');
                    isValid = false;
                }

                if ($('#bio').val().length > 500) {
                    $('#bioError').text('Bio must be 500 characters or fewer.');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    // Scroll to first error (if any)
                    const $firstError = $('.error-message:not(:empty)').first().closest('div');
                    if ($firstError.length) {
                        $('html, body').animate({
                            scrollTop: $firstError.offset().top - 20
                        }, 400);
                    }
                }
            });

            // initialize preview once on load
            updatePreview();
        });
    </script>

<?php endif; ?>

</body>
</html>
