<?php
// index.php
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
    <title><?php echo $is_submitted ? "Submission Success — Guided by Hema Ma'am" : "Student Registration Form — Guided by Hema Ma'am"; ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* fonts & base */
        body { font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; background: linear-gradient(180deg,#f8fafc 0%, #eef2ff 100%); }

        /* subtle focus for accessibility */
        .focus-ring:focus { outline: none; box-shadow: 0 0 0 6px rgba(99,102,241,0.10); border-color: #6366f1; }

        /* modal animations */
        @keyframes popIn {
            0% { transform: translateY(10px) scale(.98); opacity: 0; }
            60% { transform: translateY(-6px) scale(1.02); opacity: 1; }
            100% { transform: translateY(0) scale(1); opacity: 1; }
        }

        .modal-enter { animation: popIn 420ms cubic-bezier(.2,.9,.3,1) both; }

        /* confetti/emojis falling */
        .confetti {
            pointer-events: none;
            position: fixed;
            inset: 0;
            z-index: 60;
            overflow: hidden;
        }
        .confetti span {
            position: absolute;
            top: -6%;
            font-size: 1.25rem;
            opacity: 0.95;
            transform-origin: center;
            animation: fall linear infinite, sway ease-in-out infinite;
        }
        @keyframes fall {
            to { transform: translateY(120vh) rotate(720deg); opacity: 0.95; }
        }
        @keyframes sway {
            0% { transform: translateX(0) rotate(0deg); }
            50% { transform: translateX(10px) rotate(180deg); }
            100% { transform: translateX(0) rotate(360deg); }
        }

        /* responsive max height for preview container */
        .h-fit-max { max-height: 78vh; overflow: auto; }

        /* small card animation */
        .float-up { animation: floatUp 3.8s ease-in-out infinite; }
        @keyframes floatUp {
            0% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
            100% { transform: translateY(0); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- FORM CARD -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-2xl border border-gray-100">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1">Student Registration Form</h1>
                <div class="text-indigo-600 font-medium">Guided by Hema Ma'am ✨</div>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-400">Quick • Responsive • Animated</div>
                <div class="text-2xl">🎓</div>
            </div>
        </div>

        <p class="text-gray-500 mt-4 mb-6">Fill the form to register. Preview updates in real-time — no data is submitted from preview. 🚀</p>

        <form id="registrationForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-5" novalidate>

            <!-- Full Name -->
            <div>
                <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="fullName" name="fullName" required
                       value="<?php echo htmlspecialchars($fullName); ?>"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                       placeholder="e.g., John Doe" aria-describedby="fullNameError">
                <div id="fullNameError" class="error-message text-red-500 text-sm mt-1"><?php echo $errors['fullName'] ?? ''; ?></div>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($email); ?>"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                       placeholder="you@example.com" aria-describedby="emailError">
                <div id="emailError" class="error-message text-red-500 text-sm mt-1"><?php echo $errors['email'] ?? ''; ?></div>
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" id="phone" name="phone" required
                       value="<?php echo htmlspecialchars($phone); ?>"
                       class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                       placeholder="+91 9876543210" aria-describedby="phoneError" inputmode="tel">
                <div id="phoneError" class="error-message text-red-500 text-sm mt-1"><?php echo $errors['phone'] ?? ''; ?></div>
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
                <div id="programError" class="error-message text-red-500 text-sm mt-1"><?php echo $errors['program'] ?? ''; ?></div>
            </div>

            <!-- Bio -->
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700">Short Bio <span class="text-xs text-gray-400">(optional, max 500 chars)</span></label>
                <textarea id="bio" name="bio" rows="4" maxlength="500"
                          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring transition duration-150 ease-in-out"
                          placeholder="Tell us a bit about yourself..."><?php echo htmlspecialchars($bio); ?></textarea>
                <div class="flex justify-between items-center mt-1">
                    <div id="bioError" class="error-message text-red-500 text-sm"><?php echo $errors['bio'] ?? ''; ?></div>
                    <div class="text-sm text-gray-500"><span id="bioCount"><?php echo mb_strlen($bio); ?></span>/500</div>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-2">
                <button type="submit"
                        class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-lg text-lg font-semibold text-white bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                    <span class="text-2xl">🚀</span> <span>Submit Application</span>
                </button>
            </div>
        </form>

        <p class="mt-4 text-xs text-gray-500 text-center">You can preview your entry in real-time on the right. All data is validated before submit.</p>
    </div>

    <!-- LIVE PREVIEW -->
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-2xl border border-gray-100 h-fit">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-700">Live Preview</h2>
            <div class="text-sm text-gray-400">Preview only • Not submitted</div>
        </div>

        <div id="previewCard" class="space-y-3 h-fit-max p-3 rounded-xl border border-dashed border-gray-100">
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
                <div class="text-xs text-gray-400">Preview doesn't submit data — it's just for display. ✨</div>
            </div>
        </div>
    </div>
</div>

<!-- CONFETTI (server-side shown on successful submit) -->
<?php if ($is_submitted): ?>
    <div class="confetti" id="confettiContainer" aria-hidden="true">
        <?php
        // generate a few emoji spans with random left positions and durations
        $emojis = ['🎉','✨','🎊','🥳','💫','🌟','🟣','💜'];
        for ($i = 0; $i < 18; $i++):
            $left = rand(2, 98);
            $size = rand(16, 28);
            $delay = rand(0, 2000);
            $dur = rand(3500, 6500);
            $emoji = $emojis[array_rand($emojis)];
        ?>
        <span style="left:<?php echo $left; ?>%; font-size:<?php echo $size; ?>px; animation-duration:<?php echo $dur; ?>ms; animation-delay:<?php echo $delay; ?>ms;">
            <?php echo $emoji; ?>
        </span>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<!-- SUCCESS MODAL (popup) -->
<div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 <?php echo $is_submitted ? '' : 'pointer-events-none opacity-0'; ?>" aria-hidden="<?php echo $is_submitted ? 'false' : 'true'; ?>">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

    <div class="relative w-full max-w-lg modal-enter bg-white rounded-2xl shadow-2xl border border-indigo-100 p-6 md:p-8 z-50 transform transition-all">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-500 to-pink-500 flex items-center justify-center text-white text-3xl">🎉</div>
            </div>
            <div class="flex-1">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-2xl font-extrabold text-indigo-700">Woohoo! Application Submitted</h3>
                        <p class="text-indigo-600 font-medium mt-1">Guided by Hema Ma'am</p>
                    </div>
                    <button id="closeModal" aria-label="Close" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="bg-indigo-50 p-4 rounded-lg mt-4 border-l-4 border-indigo-500">
                    <p class="text-sm font-semibold text-indigo-700">Your Reference ID</p>
                    <p class="text-2xl font-black text-indigo-900 mt-1"><?php echo htmlspecialchars($appId); ?></p>
                </div>

                <h4 class="text-lg font-semibold text-gray-700 mt-4">Summary</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="text-xs text-gray-500">Name</div>
                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($fullName); ?></div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="text-xs text-gray-500">Email</div>
                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($email); ?></div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <div class="text-xs text-gray-500">Phone</div>
                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($phone); ?></div>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg">
                        <div class="text-xs text-gray-500">Program</div>
                        <div class="font-bold text-green-700"><?php echo htmlspecialchars($programName); ?></div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-xs text-gray-500">Bio</div>
                    <div class="text-gray-800 mt-1"><?php echo nl2br(htmlspecialchars($bio ?: '—')); ?></div>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="inline-flex items-center gap-2 justify-center py-2 px-4 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                        Register Another
                    </a>
                    <button onclick="window.print();" class="inline-flex items-center gap-2 justify-center py-2 px-4 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        Print / Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript: client-side validation + live preview + modal logic -->
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
            } else {
                // allow regular form submit; server will re-render with modal
                // Optionally could show an immediate client-side modal while waiting, but we rely on server confirm.
            }
        });

        // initialize preview once on load
        updatePreview();

        // Modal close action (when modal present after server submit)
        $('#closeModal').on('click', function() {
            // hide modal visually
            $('#successModal').addClass('pointer-events-none opacity-0').attr('aria-hidden', 'true');
            $('#confettiContainer').remove(); // stop confetti
        });

        // If server indicated submission, show a temporary pulse on modal and focus on it
        <?php if ($is_submitted): ?>
        setTimeout(function() {
            const $modal = $('#successModal .modal-enter');
            $modal.addClass('float-up');
            // focus the close button for accessibility
            $('#closeModal').focus();
        }, 180);
        <?php endif; ?>
    });
</script>

</body>
</html>
