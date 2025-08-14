<?php 
/**
 * Template Name: Form Submission
 */

// Submission code 
require_once 'config.php';

function createTableIfNotExists($conn) {
    $createTableSQL = "CREATE TABLE IF NOT EXISTS personal_info (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        gender ENUM('Male', 'Female', 'Other') NOT NULL,
        age INT(3) NOT NULL,
        street_address TEXT NOT NULL,
        city VARCHAR(100) NOT NULL,
        zip_code VARCHAR(20),
        state_province VARCHAR(100) NOT NULL,
        country VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        skill_set VARCHAR(100) NOT NULL,
        specify_other VARCHAR(200),
        hips VARCHAR(50),
        hair_color VARCHAR(50),
        eye_color VARCHAR(50),
        height VARCHAR(20),
        weight VARCHAR(20),
        bust VARCHAR(20),
        waist VARCHAR(20),
        youtube_url TEXT,
        instagram_url TEXT,
        head_shot LONGBLOB,
        head_shot_name VARCHAR(255),
        head_shot_type VARCHAR(100),
        full_body_shot LONGBLOB,
        full_body_shot_name VARCHAR(255),
        full_body_shot_type VARCHAR(100),
        profile_shot LONGBLOB,
        profile_shot_name VARCHAR(255),
        profile_shot_type VARCHAR(100),
        editorial_shot LONGBLOB,
        editorial_shot_name VARCHAR(255),
        editorial_shot_type VARCHAR(100),
        intro_video LONGBLOB,
        intro_video_name VARCHAR(255),
        intro_video_type VARCHAR(100),
        runway_video LONGBLOB,
        runway_video_name VARCHAR(255),
        runway_video_type VARCHAR(100),
        bio TEXT NOT NULL,
        cv LONGBLOB NOT NULL,
        cv_name VARCHAR(255) NOT NULL,
        cv_type VARCHAR(100) NOT NULL,
        portfolio_link TEXT,
        terms_accepted BOOLEAN NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!mysqli_query($conn, $createTableSQL)) {
        die("Error creating table: " . mysqli_error($conn));
    }
}

// Function to handle file validation and read content
function validateAndReadFile($file, $allowedTypes, $maxSize = 10485760) { // 10MB default
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        throw new Exception("File size exceeds maximum limit of " . ($maxSize / 1048576) . "MB");
    }
    
    // Check file type
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("Invalid file type. Allowed types: " . implode(', ', $allowedTypes));
    }
    
    // Read file content
    $fileContent = file_get_contents($file['tmp_name']);
    if ($fileContent === false) {
        throw new Exception("Failed to read file content");
    }
    
    return [
        'content' => $fileContent,
        'name' => $file['name'],
        'type' => $file['type'],
        'size' => $file['size']
    ];
}

// Function to validate and sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_personal_info') {
    
    // Verify nonce for WordPress (if using WordPress)
    if (function_exists('wp_verify_nonce')) {
        if (!wp_verify_nonce($_POST['personal_info_nonce'], 'personal_info_form')) {
            die('Security check failed');
        }
    }
    
    try {
        // Create table if it doesn't exist
        createTableIfNotExists($conn);
        
        // Sanitize and validate input data
        $firstName = sanitizeInput($_POST['firstName']);
        $lastName = sanitizeInput($_POST['lastName']);
        $gender = sanitizeInput($_POST['gender']);
        $age = (int)$_POST['age'];
        $streetAddress = sanitizeInput($_POST['streetAddress']);
        $city = sanitizeInput($_POST['city']);
        $zipCode = sanitizeInput($_POST['zipCode'] ?? '');
        $stateProvince = sanitizeInput($_POST['stateProvince']);
        $country = sanitizeInput($_POST['country']);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $phone = sanitizeInput($_POST['phone']);
        $skillSet = sanitizeInput($_POST['skillSet']);
        $specifyOther = sanitizeInput($_POST['specifyOther'] ?? '');
        $youtubeUrl = filter_var($_POST['youtubeUrl'] ?? '', FILTER_SANITIZE_URL);
        $instagramUrl = filter_var($_POST['instagramUrl'] ?? '', FILTER_SANITIZE_URL);
        $bio = sanitizeInput($_POST['bio']);
        $portfolioLink = filter_var($_POST['portfolioLink'] ?? '', FILTER_SANITIZE_URL);
        $termsAccepted = isset($_POST['termsAccepted']) ? 1 : 0;
        
        // Handle silhouette profile data (conditional)
        $hips = '';
        $hairColor = '';
        $eyeColor = '';
        $height = '';
        $weight = '';
        $bust = '';
        $waist = '';

        if ($skillSet === 'models' || $skillSet === 'actors') {
            $hips = sanitizeInput($_POST['hips'] ?? '');
            $hairColor = sanitizeInput($_POST['hairColor'] ?? '');
            $eyeColor = sanitizeInput($_POST['eyeColor'] ?? '');
            $height = sanitizeInput($_POST['height'] ?? '');
            $weight = sanitizeInput($_POST['weight'] ?? '');
            $bust = sanitizeInput($_POST['bust'] ?? '');
            $waist = sanitizeInput($_POST['waist'] ?? '');
        }
        
        // Validate required fields
        if (empty($firstName) || empty($lastName) || empty($email) || empty($bio)) {
            throw new Exception("Please fill in all required fields");
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Validate age
        if ($age < 16 || $age > 100) {
            throw new Exception("Age must be between 16 and 100");
        }
        
        // Check if terms are accepted
        if (!$termsAccepted) {
            throw new Exception("You must accept the Terms & Conditions");
        }
        
        // Initialize file variables
        $headShotData = null; $headShotName = null; $headShotType = null;
        $fullBodyShotData = null; $fullBodyShotName = null; $fullBodyShotType = null;
        $profileShotData = null; $profileShotName = null; $profileShotType = null;
        $editorialShotData = null; $editorialShotName = null; $editorialShotType = null;
        $introVideoData = null; $introVideoName = null; $introVideoType = null;
        $runwayVideoData = null; $runwayVideoName = null; $runwayVideoType = null;
        $cvData = null; $cvName = null; $cvType = null;
        
        // Handle file uploads and validation
        $imageTypes = ['jpg', 'jpeg'];
        $videoTypes = ['mp4'];
        $cvTypes = ['pdf', 'doc', 'docx'];
        
        // Process image uploads
        if (isset($_FILES['headShot'])) {
            $fileData = validateAndReadFile($_FILES['headShot'], $imageTypes);
            if ($fileData) {
                $headShotData = $fileData['content'];
                $headShotName = $fileData['name'];
                $headShotType = $fileData['type'];
            }
        }
        
        if (isset($_FILES['fullBodyShot'])) {
            $fileData = validateAndReadFile($_FILES['fullBodyShot'], $imageTypes);
            if ($fileData) {
                $fullBodyShotData = $fileData['content'];
                $fullBodyShotName = $fileData['name'];
                $fullBodyShotType = $fileData['type'];
            }
        }
        
        if (isset($_FILES['profileShot'])) {
            $fileData = validateAndReadFile($_FILES['profileShot'], $imageTypes);
            if ($fileData) {
                $profileShotData = $fileData['content'];
                $profileShotName = $fileData['name'];
                $profileShotType = $fileData['type'];
            }
        }
        
        if (isset($_FILES['editorialShot'])) {
            $fileData = validateAndReadFile($_FILES['editorialShot'], $imageTypes);
            if ($fileData) {
                $editorialShotData = $fileData['content'];
                $editorialShotName = $fileData['name'];
                $editorialShotType = $fileData['type'];
            }
        }
        
        // Process video uploads
        if (isset($_FILES['introVideo'])) {
            $fileData = validateAndReadFile($_FILES['introVideo'], $videoTypes);
            if ($fileData) {
                $introVideoData = $fileData['content'];
                $introVideoName = $fileData['name'];
                $introVideoType = $fileData['type'];
            }
        }
        
        if (isset($_FILES['runwayVideo'])) {
            $fileData = validateAndReadFile($_FILES['runwayVideo'], $videoTypes);
            if ($fileData) {
                $runwayVideoData = $fileData['content'];
                $runwayVideoName = $fileData['name'];
                $runwayVideoType = $fileData['type'];
            }
        }
        
        // Process CV upload (required)
        if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $fileData = validateAndReadFile($_FILES['cv'], $cvTypes);
            if ($fileData) {
                $cvData = $fileData['content'];
                $cvName = $fileData['name'];
                $cvType = $fileData['type'];
            } else {
                throw new Exception("CV upload is required");
            }
        } else {
            throw new Exception("CV upload is required");
        }
        
        // Prepare SQL statement
        $sql = "INSERT INTO personal_info (
            first_name, last_name, gender, age, street_address, city, zip_code, 
            state_province, country, email, phone, skill_set, specify_other,
            hips, hair_color, eye_color, height, weight, bust, waist,
            youtube_url, instagram_url, 
            head_shot, head_shot_name, head_shot_type,
            full_body_shot, full_body_shot_name, full_body_shot_type,
            profile_shot, profile_shot_name, profile_shot_type,
            editorial_shot, editorial_shot_name, editorial_shot_type,
            intro_video, intro_video_name, intro_video_type,
            runway_video, runway_video_name, runway_video_type,
            bio, cv, cv_name, cv_type, portfolio_link, terms_accepted
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "sssisssssssssssssssssssssssssssssssssssssssssi", 
            $firstName, $lastName, $gender, $age, $streetAddress, $city, $zipCode,
            $stateProvince, $country, $email, $phone, $skillSet, $specifyOther,
            $hips, $hairColor, $eyeColor, $height, $weight, $bust, $waist,
            $youtubeUrl, $instagramUrl,
            $headShotData, $headShotName, $headShotType,
            $fullBodyShotData, $fullBodyShotName, $fullBodyShotType,
            $profileShotData, $profileShotName, $profileShotType,
            $editorialShotData, $editorialShotName, $editorialShotType,
            $introVideoData, $introVideoName, $introVideoType,
            $runwayVideoData, $runwayVideoName, $runwayVideoType,
            $bio, $cvData, $cvName, $cvType, $portfolioLink, $termsAccepted
        );
        
        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            $insertId = mysqli_insert_id($conn);
            
            // Success response
            echo json_encode([
                'success' => true,
                'message' => 'Form submitted successfully!',
                'id' => $insertId
            ]);
            
            // Redirect to success page (optional)
            // header('Location: success.php?id=' . $insertId);
            // exit;
            
        } else {
            throw new Exception("Error executing query: " . mysqli_stmt_error($stmt));
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
        
    } catch (Exception $e) {
        // Error response
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
        
        // Log error (optional)
        error_log("Form submission error: " . $e->getMessage());
    }
    
    // Close connection
    mysqli_close($conn);
    
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information Form</title>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const skillSetSelect = document.querySelector('select[name="skillSet"]');
        const silhouetteSection = document.getElementById('silhouette-profile');

        function toggleSilhouetteSection() {
            const selectedValue = skillSetSelect.value.toLowerCase();
            if (selectedValue === "models" || selectedValue === "actors") {
                silhouetteSection.style.display = "block";
            } else {
                silhouetteSection.style.display = "none";
            }
        }

        // Initial check on page load
        toggleSilhouetteSection();

        // Listen for changes
        skillSetSelect.addEventListener("change", toggleSilhouetteSection);

        // Add file size validation
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                if (this.files[0] && this.files[0].size > maxSize) {
                    alert('File size exceeds 10MB limit. Please choose a smaller file.');
                    this.value = '';
                }
            });
        });
    });
    </script>

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Gantari;
        background-color: #1a1a1a;
        color: white;
        padding: 40px;
    }

    .form-container {
        max-width: 1200px;
        margin: 0 auto;
        background-color: #2d2d2d;
        border-radius: 12px;
        padding: 40px;
    }

    h2 {
        font-size: 32px;
        font-weight: normal;
        margin-bottom: 20px;
    }

    .subtitle {
        font-size: 16px;
        margin-bottom: 30px;
    }

    .row {
        display: flex;
        gap: 30px;
        margin-bottom: 25px;
        align-items: center;
    }

    .field {
        flex: 1;
    }

    label {
        display: block;
        font-size: 16px;
        margin-bottom: 8px;
    }

    .submit-button {
        display: flex;
        justify-content: center;
        margin: 40px 0;
        width: 100%;
        padding: 12px 0;
        cursor: pointer;
        background-color: #ff4757;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 18px;
    }

    .required {
        color: #ff4757;
    }

    .newClass {
        display: flex;
        gap: 10px;
    }

    .file-input {
        height: auto;
        padding: 10px 10px;
    }

    input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: red;
    }

    input,
    select,
    textarea {
        width: 100%;
        height: 45px;
        padding: 0 15px;
        background-color: #f5f5f5;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        color: #333;
    }

    textarea {
        height: 120px;
        padding: 15px;
        resize: vertical;
    }

    select {
        cursor: pointer;
    }

    .divider {
        height: 5px;
        background-color: #444;
        margin: 40px 0;
    }

    @media (max-width: 768px) {
        body {
            padding: 20px;
        }

        .form-container {
            padding: 20px;
        }

        .row {
            flex-direction: column;
            gap: 15px;
        }

        h2 {
            font-size: 24px;
        }
    }
    </style>
</head>

<body>
    <div class="form-container">
        <form method="POST" action="<?php echo esc_url(get_permalink()); ?>" enctype="multipart/form-data">

            <?php wp_nonce_field('personal_info_form', 'personal_info_nonce'); ?>
            <input type="hidden" name="action" value="submit_personal_info">

            <!-- Personal Information Section -->
            <h2>Personal Information</h2>
            <p class="subtitle">All Fields Are Mandatory</p>

            <div class="row">
                <div class="field">
                    <label>First Name <span class="required">*</span></label>
                    <input type="text" name="firstName" required>
                </div>
                <div class="field">
                    <label>Last Name <span class="required">*</span></label>
                    <input type="text" name="lastName" required>
                </div>
            </div>

            <div class="row">
                <div class="field">
                    <label>Gender <span class="required">*</span></label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="field">
                    <label>Age <span class="required">*</span></label>
                    <input type="number" name="age" min="16" max="100" required>
                </div>
            </div>

            <div class="row">
                <div class="field">
                    <label>Street Address <span class="required">*</span></label>
                    <input type="text" name="streetAddress" required>
                </div>
                <div class="field">
                    <label>City <span class="required">*</span></label>
                    <input type="text" name="city" required>
                </div>
                <div class="field">
                    <label>Zip Code</label>
                    <input type="text" name="zipCode">
                </div>
                <div class="field">
                    <label>State/Province <span class="required">*</span></label>
                    <input type="text" name="stateProvince" required>
                </div>
                <div class="field">
                    <label>Country <span class="required">*</span></label>
                    <input type="text" name="country" required>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Contact Information Section -->
            <h2>Contact Information</h2>

            <div class="row">
                <div class="field">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" required>
                </div>
                <div class="field">
                    <label>Phone <span class="required">*</span></label>
                    <input type="tel" name="phone" required>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Skill Set Section -->
            <h2>Skill Set</h2>

            <div class="row">
                <div class="field">
                    <label>Skill Set <span class="required">*</span></label>
                    <select name="skillSet" required>
                        <option value="">Select Skill Set</option>
                        <option value="models">Models</option>
                        <option value="fashionDesigners">Fashion Designers</option>
                        <option value="costumeDesigners">Costume Designers</option>
                        <option value="fashionChoreographers">Fashion Choreographers</option>
                        <option value="fashionMakeupArtists">Fashion Makeup Artists</option>
                        <option value="actors">Actors</option>
                        <option value="directors">Directors</option>
                        <option value="producers">Producers</option>
                        <option value="scriptWriters">Script Writers</option>
                        <option value="editors">Editors</option>
                        <option value="voiceOverArtists">Voice Over Artists</option>
                        <option value="vfxArtists">VFX Artists</option>
                        <option value="musicians">Musicians</option>
                        <option value="singers">Singers</option>
                        <option value="choreographers">Choreographers</option>
                        <option value="productionDesigners">Production Designers</option>
                        <option value="cinematographers">Cinematographers</option>
                        <option value="filmMakeupArtists">Film Makeup Artists</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="field">
                    <label>Specify, Any other</label>
                    <input type="text" name="specifyOther">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Silhouette Profile Section (Conditional) -->
            <div id="silhouette-profile" style="display: none;">
                <h2>Silhouette Profile</h2>
                <div class="row">
                    <div class="field">
                        <label>Hips <span class="required">*</span></label>
                        <input type="text" name="hips">
                    </div>
                    <div class="field">
                        <label>Hair Color <span class="required">*</span></label>
                        <input type="text" name="hairColor">
                    </div>
                    <div class="field">
                        <label>Eye Color <span class="required">*</span></label>
                        <input type="text" name="eyeColor">
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Height <span class="required">*</span></label>
                        <input type="text" name="height">
                    </div>
                    <div class="field">
                        <label>Weight <span class="required">*</span></label>
                        <input type="text" name="weight">
                    </div>
                    <div class="field">
                        <label>Bust <span class="required">*</span></label>
                        <input type="text" name="bust">
                    </div>
                    <div class="field">
                        <label>Waist <span class="required">*</span></label>
                        <input type="text" name="waist">
                    </div>
                </div>
                <div class="divider"></div>
            </div>

            <!-- Social Links Section -->
            <h2>Social Links</h2>

            <div class="row">
                <div class="field">
                    <label>YouTube URL</label>
                    <input type="url" name="youtubeUrl">
                </div>
                <div class="field">
                    <label>Instagram URL</label>
                    <input type="url" name="instagramUrl">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Portfolio Shots Section -->
            <h2>Portfolio Shots</h2>
            <p class="subtitle">The maximum file size allowed is 10 MB, and only in JPG format</p>

            <div class="row">
                <div class="field">
                    <label>Head Shot</label>
                    <input class="file-input" type="file" name="headShot" accept=".jpg,.jpeg">
                </div>
                <div class="field">
                    <label>Full Body Shot</label>
                    <input class="file-input" type="file" name="fullBodyShot" accept=".jpg,.jpeg">
                </div>
                <div class="field">
                    <label>Profile Shot</label>
                    <input class="file-input" type="file" name="profileShot" accept=".jpg,.jpeg">
                </div>
                <div class="field">
                    <label>Editorial Shot</label>
                    <input class="file-input" type="file" name="editorialShot" accept=".jpg,.jpeg">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Screen Clips Section -->
            <h2>Screen Clips</h2>
            <p class="subtitle">The maximum file size allowed is 10 MB, and only in MP4 format</p>

            <div class="row">
                <div class="field">
                    <label>Introduction Video</label>
                    <input class="file-input" type="file" name="introVideo" accept=".mp4">
                </div>
                <div class="field">
                    <label>Runway Recaps Video</label>
                    <input class="file-input" type="file" name="runwayVideo" accept=".mp4">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Bio Section -->
            <div class="row">
                <div class="field">
                    <label>Bio <span class="required">*</span></label>
                    <textarea name="bio" required placeholder="Tell us about yourself..."></textarea>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Portfolio Upload Section -->
            <h2>Portfolio Upload</h2>
            <p class="subtitle">The maximum file size allowed is 10 MB</p>

            <div class="row">
                <div class="field">
                    <label>Upload CV <span class="required">*</span></label>
                    <input class="file-input" type="file" name="cv" accept=".pdf,.doc,.docx" required>
                </div>
                <div class="field">
                    <label>Portfolio Link</label>
                    <input type="url" name="portfolioLink">
                </div>
            </div>

            <div class="divider"></div>

            <!-- Terms & Conditions Section -->
            <h2>Terms & Conditions</h2>
            <div class="row">
                <div class="field">
                    <label>All the fields are mandatory <span class="required">*</span></label>
                    <div class="newClass">
                        <div>
                            <input type="checkbox" name="termsAccepted" id="termsAccepted" required>
                        </div>
                        <div>
                            <label for="termsAccepted">Accept "Terms & Conditions and Privacy Policy"</label>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-button">Submit</button>
        </form>
    </div>
</body>

</html>