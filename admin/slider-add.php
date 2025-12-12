<?php require_once('header.php'); ?>
<?php require_once('../includes/file_optimizer.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	// Check if cropped image data exists
	if(empty($_POST['cropped_image'])) {
		$valid = 0;
		$error_message .= 'Please select and crop an image<br>';
	}

	if($valid == 1) {
		// Process the cropped image
		$croppedImage = $_POST['cropped_image'];
		
		// Remove the data:image/png;base64, part
		$imageData = explode(',', $croppedImage);
		$imageData = base64_decode($imageData[1]);
		
		// Create image from string
		$image = imagecreatefromstring($imageData);
		
		if($image !== false) {
			// Get auto increment id
			$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_slider'");
			$statement->execute();
			$result = $statement->fetchAll();
			foreach($result as $row) {
				$ai_id=$row[10];
			}
			
			// Create temporary file
			$tempFile = sys_get_temp_dir() . '/slider_temp_' . uniqid() . '.png';
			imagepng($image, $tempFile);
			imagedestroy($image);
			
			// Use FileOptimizer to convert to WebP with maxWidth=1920 to preserve quality
			$uploadDir = '../assets/uploads/sliders/';
			$destPath = $uploadDir . 'slider-' . $ai_id . '.webp';

			$success = FileOptimizer::optimizeImage($tempFile, $destPath, 1920, 85);

			if ($success) {
				$optimizedFilename = 'slider-' . $ai_id . '.webp';
			} else {
				// Fallback to PNG if optimization fails
				$destPathPng = $uploadDir . 'slider-' . $ai_id . '.png';
				if (copy($tempFile, $destPathPng)) {
					$optimizedFilename = 'slider-' . $ai_id . '.png';
				} else {
					$optimizedFilename = false;
				}
			}
			
			// Clean up temp file
			if(file_exists($tempFile)) {
				unlink($tempFile);
			}
			
			if($optimizedFilename) {
				// Insert into database
				$statement = $pdo->prepare("INSERT INTO tbl_slider (photo,heading) VALUES (?,?)");
				$statement->execute(array($optimizedFilename,$_POST['heading']));
				
				$success_message = 'Slider added successfully!';
				unset($_POST['heading']);
			} else {
				$error_message .= 'Failed to save slider image<br>';
			}
		} else {
			$error_message .= 'Failed to process cropped image<br>';
		}
	}
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
/* Clean Minimal Layout */
.upload-wizard {
	max-width: 900px;
	margin: 0 auto;
}

.step-indicator {
	display: flex;
	justify-content: space-between;
	margin-bottom: 30px;
	padding: 20px 30px;
	background: #ffffff;
	border-radius: 8px;
	border: 2px solid #e5e7eb;
}

.step {
	flex: 1;
	text-align: center;
	position: relative;
	color: #9ca3af;
	font-weight: 600;
	transition: all 0.3s ease;
	font-size: 14px;
}

.step.active {
	color: #1f2937;
}

.step.completed {
	color: #10b981;
}

.step-number {
	display: inline-block;
	width: 36px;
	height: 36px;
	line-height: 36px;
	border-radius: 50%;
	background: #f3f4f6;
	border: 2px solid #e5e7eb;
	margin-bottom: 8px;
	font-size: 16px;
	transition: all 0.3s ease;
	color: #9ca3af;
}

.step.active .step-number {
	background: #1f2937;
	color: #ffffff;
	border-color: #1f2937;
}

.step.completed .step-number {
	background: #10b981;
	color: #ffffff;
	border-color: #10b981;
}

.step.completed .step-number::before {
	content: '✓';
}

.upload-section {
	display: none;
	animation: fadeInUp 0.3s ease;
}

.upload-section.active {
	display: block;
}

@keyframes fadeInUp {
	from {
		opacity: 0;
		transform: translateY(15px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

/* File Upload Area */
.file-upload-area {
	border: 2px dashed #d1d5db;
	border-radius: 8px;
	padding: 60px 40px;
	text-align: center;
	background: #ffffff;
	transition: all 0.3s ease;
	cursor: pointer;
}

.file-upload-area:hover {
	border-color: #6b7280;
	background: #f9fafb;
}

.file-upload-area.dragover {
	border-color: #10b981;
	background: #f0fdf4;
}

.upload-icon {
	font-size: 56px;
	color: #6b7280;
	margin-bottom: 20px;
}

.upload-text {
	font-size: 18px;
	font-weight: 600;
	color: #1f2937;
	margin-bottom: 8px;
}

.upload-hint {
	color: #6b7280;
	font-size: 14px;
}

#photo-input {
	display: none;
}

/* Crop Section */
.crop-container {
	background: #ffffff;
	border-radius: 8px;
	padding: 30px;
	border: 2px solid #e5e7eb;
	margin-bottom: 20px;
}

.crop-preview-box {
	background: #000000;
	border-radius: 6px;
	overflow: hidden;
	margin-bottom: 20px;
	border: 1px solid #d1d5db;
}

#image-preview {
	max-width: 100%;
	display: block;
	margin: 0 auto;
}

.crop-controls {
	display: flex;
	gap: 10px;
	justify-content: center;
	flex-wrap: wrap;
}

.btn-modern {
	padding: 10px 24px;
	border: 2px solid transparent;
	border-radius: 6px;
	font-weight: 600;
	font-size: 14px;
	cursor: pointer;
	transition: all 0.2s ease;
	display: inline-flex;
	align-items: center;
	gap: 8px;
}

.btn-modern:hover {
	transform: translateY(-1px);
}

.btn-modern:active {
	transform: translateY(0);
}

.btn-primary-modern {
	background: #1f2937;
	color: #ffffff;
	border-color: #1f2937;
}

.btn-primary-modern:hover {
	background: #111827;
	border-color: #111827;
}

.btn-success-modern {
	background: #10b981;
	color: #ffffff;
	border-color: #10b981;
}

.btn-success-modern:hover {
	background: #059669;
	border-color: #059669;
}

.btn-warning-modern {
	background: #f59e0b;
	color: #ffffff;
	border-color: #f59e0b;
}

.btn-warning-modern:hover {
	background: #d97706;
	border-color: #d97706;
}

.btn-secondary-modern {
	background: #ffffff;
	color: #1f2937;
	border-color: #d1d5db;
}

.btn-secondary-modern:hover {
	background: #f9fafb;
	border-color: #9ca3af;
}

/* Preview Section */
.preview-container {
	background: #ffffff;
	border: 2px solid #10b981;
	border-radius: 8px;
	padding: 30px;
	margin-bottom: 20px;
}

.preview-header {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 20px;
	color: #059669;
}

.preview-header i {
	font-size: 24px;
}

.preview-header h3 {
	margin: 0;
	font-size: 20px;
	font-weight: 700;
}

.preview-image-box {
	background: #f9fafb;
	border-radius: 6px;
	padding: 20px;
	border: 1px solid #e5e7eb;
}

#crop-result-preview {
	max-width: 100%;
	height: auto;
	border-radius: 4px;
	display: block;
	margin: 0 auto;
}

.preview-info {
	text-align: center;
	margin-top: 15px;
	padding: 10px;
	background: #f0fdf4;
	border-radius: 6px;
	border: 1px solid #d1fae5;
}

.preview-info strong {
	color: #059669;
	font-size: 14px;
}

/* Final Section */
.final-section {
	background: #ffffff;
	border-radius: 8px;
	padding: 30px;
	border: 2px solid #e5e7eb;
}

.form-group-modern {
	margin-bottom: 20px;
}

.form-group-modern label {
	display: block;
	font-weight: 600;
	color: #1f2937;
	margin-bottom: 8px;
	font-size: 14px;
}

.form-control-modern {
	width: 100%;
	padding: 12px 16px;
	border: 2px solid #e5e7eb;
	border-radius: 6px;
	font-size: 14px;
	transition: all 0.2s ease;
	background: #ffffff;
}

.form-control-modern:focus {
	outline: none;
	border-color: #1f2937;
	background: #ffffff;
}

.action-buttons {
	display: flex;
	gap: 10px;
	justify-content: flex-end;
	margin-top: 25px;
}

/* Info Box */
.info-box {
	background: #eff6ff;
	border-left: 3px solid #3b82f6;
	padding: 14px 16px;
	border-radius: 6px;
	margin-bottom: 20px;
}

.info-box-icon {
	display: inline-block;
	margin-right: 8px;
	color: #2563eb;
	font-size: 18px;
}

.info-box-text {
	color: #1e40af;
	font-size: 13px;
	line-height: 1.5;
}

/* Responsive */
@media (max-width: 768px) {
	.step-indicator {
		padding: 15px;
	}
	
	.step-text {
		font-size: 11px;
	}
	
	.step-number {
		width: 32px;
		height: 32px;
		line-height: 32px;
		font-size: 14px;
	}
	
	.file-upload-area {
		padding: 40px 20px;
	}
	
	.crop-controls {
		flex-direction: column;
	}
	
	.btn-modern {
		width: 100%;
		justify-content: center;
	}
}
</style>

<section class="content-header">
	<div class="content-header-left">
		<h1>Add New Slider</h1>
	</div>
	<div class="content-header-right">
		<a href="slider.php" class="btn btn-primary btn-sm">
			<i class="fa fa-list"></i> View All Sliders
		</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p><i class="fa fa-exclamation-circle"></i> <?php echo $error_message; ?></p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><i class="fa fa-check-circle"></i> <?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<div class="upload-wizard">
				<!-- Step Indicator -->
				<div class="step-indicator">
					<div class="step active" id="step-1-indicator">
						<div class="step-number">1</div>
						<div class="step-text">Upload Image</div>
					</div>
					<div class="step" id="step-2-indicator">
						<div class="step-number">2</div>
						<div class="step-text">Crop & Adjust</div>
					</div>
					<div class="step" id="step-3-indicator">
						<div class="step-number">3</div>
						<div class="step-text">Preview & Submit</div>
					</div>
				</div>

				<form id="slider-form" method="post" action="">
					<input type="hidden" name="cropped_image" id="cropped-image-data">

					<!-- Step 1: Upload -->
					<div class="upload-section active" id="step-1">
						<!-- <div class="info-box">
							<i class="fa fa-info-circle info-box-icon"></i>
							<span class="info-box-text">
								<strong>Image Requirements:</strong> Your image will be automatically cropped to <strong>1920 x 280 pixels</strong>. For best results, upload high-quality landscape images (minimum 1920px wide).
							</span>
						</div> -->

						<div class="file-upload-area" id="upload-area">
							<div class="upload-icon">
								<i class="fa fa-cloud-upload"></i>
							</div>
							<div class="upload-text">Click or Drag to Upload Image</div>
							<div class="upload-hint">Supported formats: JPG, PNG, GIF (Max 10MB)</div>
							<input type="file" id="photo-input" accept="image/jpeg,image/jpg,image/png,image/gif">
						</div>
					</div>

					<!-- Step 2: Crop -->
					<div class="upload-section" id="step-2">
						<div class="crop-container">
							<h3 style="margin: 0 0 20px 0; color: #1e293b;">
								<i class="fa fa-crop"></i> Adjust Your Image
							</h3>
							<div class="crop-preview-box">
								<img id="image-preview" src="" alt="Preview">
							</div>
							<div class="crop-controls">
								<button type="button" class="btn-modern btn-success-modern" id="crop-btn">
									<i class="fa fa-check"></i> Apply Crop
								</button>
								<button type="button" class="btn-modern btn-warning-modern" id="reset-btn">
									<i class="fa fa-refresh"></i> Reset
								</button>
								<button type="button" class="btn-modern btn-secondary-modern" id="back-to-upload-btn">
									<i class="fa fa-arrow-left"></i> Change Image
								</button>
							</div>
						</div>
					</div>

					<!-- Step 3: Preview & Submit -->
					<div class="upload-section" id="step-3">
						<div class="preview-container">
							<div class="preview-header">
								<i class="fa fa-check-circle"></i>
								<h3>Perfect! Image Ready</h3>
							</div>
							<div class="preview-image-box">
								<img id="crop-result-preview" src="" alt="Final Preview">
							</div>
							<div class="preview-info">
								<strong>1920 x 280 pixels</strong> • This is how your slider will appear on the homepage
							</div>
						</div>

						<div class="final-section">
							<div class="form-group-modern">
								<label for="heading-input">
									<i class="fa fa-text-width"></i> Slider Heading (Optional)
								</label>
								<input 
									type="text" 
									id="heading-input"
									name="heading" 
									class="form-control-modern" 
									placeholder="Enter a catchy headline for your slider..."
									value="<?php if(isset($_POST['heading'])){echo htmlspecialchars($_POST['heading']);} ?>"
								>
							</div>

							<div class="action-buttons">
								<button type="button" class="btn-modern btn-secondary-modern" id="back-to-crop-btn">
									<i class="fa fa-arrow-left"></i> Back to Crop
								</button>
								<button type="submit" class="btn-modern btn-primary-modern" name="form1">
									<i class="fa fa-check-circle"></i> Add Slider
								</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
let cropper = null;
let currentStep = 1;

const photoInput = document.getElementById('photo-input');
const uploadArea = document.getElementById('upload-area');
const imagePreview = document.getElementById('image-preview');
const cropBtn = document.getElementById('crop-btn');
const resetBtn = document.getElementById('reset-btn');
const backToUploadBtn = document.getElementById('back-to-upload-btn');
const backToCropBtn = document.getElementById('back-to-crop-btn');
const cropResultPreview = document.getElementById('crop-result-preview');
const croppedImageInput = document.getElementById('cropped-image-data');

// Step Navigation
function goToStep(step) {
	// Hide all sections
	document.querySelectorAll('.upload-section').forEach(section => {
		section.classList.remove('active');
	});
	
	// Show current section
	document.getElementById(`step-${step}`).classList.add('active');
	
	// Update step indicators
	for(let i = 1; i <= 3; i++) {
		const indicator = document.getElementById(`step-${i}-indicator`);
		indicator.classList.remove('active', 'completed');
		
		if(i < step) {
			indicator.classList.add('completed');
		} else if(i === step) {
			indicator.classList.add('active');
		}
	}
	
	currentStep = step;
}

// Drag and drop
uploadArea.addEventListener('dragover', (e) => {
	e.preventDefault();
	uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
	uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
	e.preventDefault();
	uploadArea.classList.remove('dragover');
	
	const files = e.dataTransfer.files;
	if(files.length > 0) {
		photoInput.files = files;
		handleFileSelect(files[0]);
	}
});

uploadArea.addEventListener('click', () => {
	photoInput.click();
});

// File selection
photoInput.addEventListener('change', function(e) {
	const file = e.target.files[0];
	if(file) {
		handleFileSelect(file);
	}
});

function handleFileSelect(file) {
	// Validate file type
	const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
	if(!validTypes.includes(file.type)) {
		alert('❌ Please select a valid image file (JPG, PNG, or GIF)');
		photoInput.value = '';
		return;
	}

	// Validate file size (5MB)
	if(file.size > 10 * 1024 * 1024) {
		alert('❌ File size must be less than 10MB');
		photoInput.value = '';
		return;
	}

	// Read and display the image
	const reader = new FileReader();
	reader.onload = function(event) {
		imagePreview.src = event.target.result;
		
		// Destroy existing cropper if any
		if(cropper) {
			cropper.destroy();
		}

		// Go to crop step
		goToStep(2);

		// Initialize Cropper.js
		setTimeout(() => {
			cropper = new Cropper(imagePreview, {
				aspectRatio: 1920 / 280, // Updated to 280px height
				viewMode: 1,
				autoCropArea: 1,
				responsive: true,
				guides: true,
				center: true,
				highlight: true,
				cropBoxResizable: false,
				dragMode: 'move',
				background: false,
				ready: function() {
					console.log('✓ Cropper initialized');
				}
			});
		}, 100);
	};
	reader.readAsDataURL(file);
}

// Crop button
cropBtn.addEventListener('click', function() {
	if(cropper) {
		const canvas = cropper.getCroppedCanvas({
			width: 1920,
			height: 280, // Updated to 280px height
			imageSmoothingEnabled: true,
			imageSmoothingQuality: 'high'
		});

		const croppedImageData = canvas.toDataURL('image/png', 1.0);
		croppedImageInput.value = croppedImageData;
		cropResultPreview.src = croppedImageData;

		goToStep(3);
	}
});

// Reset button
resetBtn.addEventListener('click', function() {
	if(cropper) {
		cropper.reset();
	}
});

// Back buttons
backToUploadBtn.addEventListener('click', function() {
	photoInput.value = '';
	if(cropper) {
		cropper.destroy();
		cropper = null;
	}
	croppedImageInput.value = '';
	goToStep(1);
});

backToCropBtn.addEventListener('click', function() {
	goToStep(2);
});

// Form validation
document.getElementById('slider-form').addEventListener('submit', function(e) {
	if(!croppedImageInput.value) {
		e.preventDefault();
		alert('⚠️ Please complete all steps before submitting!');
		goToStep(1);
		return false;
	}
});
</script>

<?php require_once('footer.php'); ?>