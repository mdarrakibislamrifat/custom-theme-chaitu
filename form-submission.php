<?php 
/**
 * Template Name: Form Submission
 */

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
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
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