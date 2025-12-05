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
        $appId = "APP-" . rand(10000, 99999);
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Application Form</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap');
        body { font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; min-height:100vh; margin:0; }

        /* page background gradient + decorative blobs */
        .page-bg {
            background: radial-gradient(1200px 600px at 10% 20%, rgba(99,102,241,0.08), transparent 8%),
                        radial-gradient(1000px 500px at 90% 80%, rgba(16,185,129,0.05), transparent 8%),
                        linear-gradient(180deg, #f8fafc 0%, #eef2ff 50%, #fef3c7 100%);
            padding: 48px 24px;
        }

        /* subtle card animation */
        .card-entrance { animation: floatUp 0.45s ease-out both; }
        @keyframes floatUp { from { opacity:0; transform: translateY(12px); } to { opacity:1; transform:translateY(0); } }

        .error-message { color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem; }
        .focus-ring:focus { outline: none; box-shadow: 0 0 0 4px rgba(99,102,241,0.12); border-color: #6366f1; }

        /* Success shimmer + confetti dots */
        .confetti { position: absolute; pointer-events: none; width: 6px; height: 6px; border-radius: 2px; opacity: 0; animation: confettiFall 1600ms linear forwards; }
        @keyframes confettiFall {
            0% { transform: translateY(-20px) rotate(0); opacity:1; }
            100% { transform: translateY(220px) rotate(720deg); opacity:0; }
        }

        /* small responsive tweaks */
        @media (min-width: 1024px) { .container-grid { grid-template-columns: 1fr 460px; } }

        .qr-box { width: 220px; height: 220px; display:flex; align-items:center; justify-content:center; border-radius:8px; background:linear-gradient(180deg,#ffffff,#f8fafc); padding:12px; }
    </style>
</head>
<body class="page-bg flex items-center justify-center">

<?php if ($is_submitted): // Display SUCCESS page ?>

    <div class="w-full max-w-xl relative bg-gradient-to-br from-white to-indigo-50 p-6 md:p-8 rounded-2xl shadow-2xl border border-indigo-200 card-entrance overflow-hidden">
        <!-- Decorative top blob -->
        <div style="position:absolute; right:-120px; top:-80px; width:320px; height:320px; background:linear-gradient(135deg,#7c3aed,#06b6d4); filter:blur(48px); opacity:0.12; transform:rotate(25deg);"></div>

        <div class="text-center mb-5">
            <span class="text-6xl mb-2 block">🎉</span>
            <h1 class="text-2xl md:text-3xl font-extrabold text-indigo-700 mt-2">Application Submitted</h1>
            <p class="text-gray-600 mt-2">Thanks for applying — we've recorded your details.</p>
        </div>

        <div class="bg-white/60 backdrop-blur-sm p-4 rounded-lg mb-4 border-l-4 border-indigo-500">
            <p class="text-sm font-semibold text-indigo-700">Your Application Number</p>
            <div class="flex items-center justify-between mt-1">
                <p class="text-2xl font-black text-indigo-900" id="appNumberDisplay"><?php echo htmlspecialchars($appId); ?></p>
                <div class="flex gap-2">
                    <button id="copyAppBtn" class="py-1 px-3 border rounded-md text-sm text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Copy</button>
                    <button id="downloadJsonBtn" class="py-1 px-3 border rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">Download</button>
                </div>
            </div>
            <div id="copyToast" style="display:none;" class="mt-2 text-xs text-green-700">Copied to clipboard ✓</div>
        </div>

        <h3 class="text-lg font-bold text-gray-700 border-b pb-2 mb-4">Summary</h3>

        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-4 p-3 bg-gradient-to-r from-white to-green-50 rounded-lg">
                <div class="text-sm text-gray-600">Name</div>
                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($fullName); ?></div>

                <div class="text-sm text-gray-600">Email</div>
                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($email); ?></div>

                <div class="text-sm text-gray-600">Phone</div>
                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($phone); ?></div>

                <div class="text-sm text-gray-600">Program</div>
                <div class="text-bold text-green-700"><?php echo htmlspecialchars($programName); ?></div>

                <div class="text-sm text-gray-600">Bio</div>
                <div class="text-gray-800"><?php echo nl2br(htmlspecialchars($bio ?: '—')); ?></div>
            </div>
        </div>

        <!-- PAYMENT SECTION -->
        <div class="mt-6 p-4 bg-gradient-to-r from-white to-yellow-50 rounded-lg border border-yellow-100">
            <div class="flex items-center justify-between">
                <h4 class="text-md font-semibold text-gray-800">Pay Application Fee</h4>
                <div class="text-sm text-gray-500">Secure UPI payment</div>
            </div>

            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600">Enter UPI ID</label>
                    <input id="upiId" type="text" placeholder="example@upi or your@bank" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring" />
                    <div id="upiError" class="error-message"></div>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Amount (INR)</label>
                    <input id="amount" type="number" min="1" placeholder="100" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus-ring" />
                </div>

                <div class="md:col-span-3 flex gap-2 mt-2">
                    <button id="generateQrBtn" class="py-2 px-4 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Generate QR Code</button>
                    <button id="resetQrBtn" class="py-2 px-4 border rounded-lg">Reset</button>
                </div>
            </div>

            <div class="mt-4 flex flex-col md:flex-row gap-4 items-center">
                <div class="qr-box" id="qrContainer">
                    <img id="qrImage" src="" alt="QR will appear here" style="display:block; max-width:100%; max-height:100%;" />
                </div>

                <div class="flex flex-col gap-2">
                    <div class="text-sm text-gray-600">Scan to pay with any UPI app</div>
                    <div class="flex gap-2">
                        <a id="downloadQrLink" href="#" download class="py-2 px-4 border rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50">Download QR</a>
                        <a id="openUpiLink" href="#" class="py-2 px-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Open UPI App</a>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Tip: check UPI ID carefully before sharing.</div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-col md:flex-row gap-3 justify-center">
             <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="inline-block py-2 px-6 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition duration-150">New Application</a>
             <a href="#" onclick="window.print();" class="inline-block py-2 px-6 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition duration-150">Print / Save</a>
        </div>
    </div>

    <script>
        // create tiny confetti pieces for visual delight
        (function(){
            for (let i=0;i<18;i++){
                const el = document.createElement('div');
                el.className = 'confetti';
                el.style.left = (10 + Math.random()*80) + '%';
                el.style.background = ['#ef4444','#f59e0b','#10b981','#6366f1','#ec4899'][Math.floor(Math.random()*5)];
                el.style.top = (Math.random()*10) + '%';
                el.style.animationDelay = (Math.random()*300) + 'ms';
                el.style.transform = 'translateY(-10px) rotate('+(Math.random()*360)+'deg)';
                document.body.appendChild(el);
            }
        })();

        // copy application number
        document.getElementById('copyAppBtn').addEventListener('click', function(){
            const txt = document.getElementById('appNumberDisplay').innerText;
            navigator.clipboard.writeText(txt).then(function(){
                const t=document.getElementById('copyToast'); t.style.display='block'; setTimeout(()=>t.style.display='none',1800);
            });
        });

        // download registration as JSON
        document.getElementById('downloadJsonBtn').addEventListener('click', function(){
            const payload = {
                applicationNumber: document.getElementById('appNumberDisplay').innerText,
                name: <?php echo json_encode($fullName); ?>,
                email: <?php echo json_encode($email); ?>,
                phone: <?php echo json_encode($phone); ?>,
                program: <?php echo json_encode($programName); ?>,
                bio: <?php echo json_encode($bio); ?>
            };
            const blob = new Blob([JSON.stringify(payload, null, 2)], {type: 'application/json'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a'); a.href = url; a.download = payload.applicationNumber + '.json'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
        });

        // PAYMENT: generate UPI qr
        function isValidUpi(upi) {
            // basic validation: contains @ and reasonable length
            return typeof upi === 'string' && upi.indexOf('@') !== -1 && upi.length >= 3 && upi.length <= 64;
        }

        document.getElementById('generateQrBtn').addEventListener('click', function(e){
            const upi = document.getElementById('upiId').value.trim();
            const amount = document.getElementById('amount').value.trim();
            const upiErrorEl = document.getElementById('upiError');
            upiErrorEl.textContent = '';

            if (!isValidUpi(upi)) {
                upiErrorEl.textContent = 'Please enter a valid UPI ID (e.g., example@upi).';
                return;
            }

            // Build UPI deep link per NPCI standard (pa, pn, am, tn)
            const pa = encodeURIComponent(upi);
            const pn = encodeURIComponent('Sapthagiri');
            const tn = encodeURIComponent('Application Fee for ' + document.getElementById('appNumberDisplay').innerText);
            const am = amount ? encodeURIComponent(amount) : '';
            let upiUri = `upi://pay?pa=${pa}&pn=${pn}&tn=${tn}`;
            if (am) upiUri += `&am=${am}`;

            // Use a public QR generator (qrserver) to get an image
            const qrSize = 300;
            const qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=' + qrSize + 'x' + qrSize + '&data=' + encodeURIComponent(upiUri);

            const qrImage = document.getElementById('qrImage');
            qrImage.src = qrSrc;
            qrImage.alt = 'UPI QR for ' + upi;

            const downloadLink = document.getElementById('downloadQrLink');
            downloadLink.href = qrSrc;
            downloadLink.download = (document.getElementById('appNumberDisplay').innerText || 'payment') + '.png';

            const openUpi = document.getElementById('openUpiLink');
            openUpi.href = upiUri;
            openUpi.setAttribute('data-upi', upiUri);
            openUpi.onclick = function(ev){
                // try to open UPI app via intent; on desktop it may do nothing but on mobile it may open UPI app
                window.location.href = upiUri;
                ev.preventDefault();
            };
        });

        document.getElementById('resetQrBtn').addEventListener('click', function(){
            document.getElementById('upiId').value = '';
            document.getElementById('amount').value = '';
            document.getElementById('qrImage').src = '';
            document.getElementById('downloadQrLink').href = '#';
            document.getElementById('openUpiLink').href = '#';
            document.getElementById('upiError').textContent = '';
        });
    </script>

<?php else: // Display REGISTRATION FORM ?>

    <div class="w-full max-w-6xl container-grid grid gap-6">
        <!-- FORM CARD -->
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 card-entrance">
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-2 text-center">Sapthagiri — Application Form</h1>
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

            <p class="mt-4 text-xs text-gray-500 text-center">You can preview your entry in real-time on the right. All data is validated before submit.</p>
        </div>

        <!-- LIVE PREVIEW (responsive) -->
        <div class="bg-gradient-to-br from-white to-sky-50 p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 h-fit">
            <h2 class="text-lg font-bold text-gray-700 mb-4">Live Preview</h2>

            <div id="previewCard" class="space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-100 to-teal-100 flex items-center justify-center text-indigo-700 text-xl font-bold">S</div>
                    <div>
                        <div id="previewName" class="text-base font-semibold text-gray-900"><?php echo htmlspecialchars($fullName ?: 'Your Name'); ?></div>
                        <div id="previewProgram" class="text-sm text-gray-600"><?php echo htmlspecialchars($programName ?: 'Program (preview)'); ?></div>
                    </div>
                </div>

                <div class="p-3 bg-white rounded-lg">
                    <div class="text-sm text-gray-600">Email</div>
                    <div id="previewEmail" class="font-medium text-gray-800"><?php echo htmlspecialchars($email ?: 'you@example.com'); ?></div>
                </div>

                <div class="p-3 bg-white rounded-lg">
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
                    const $firstError = $('.error-message:not(:empty)').first().closest('div');
                    if ($firstError.length) {
                        $('html, body').animate({ scrollTop: $firstError.offset().top - 20 }, 400);
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
