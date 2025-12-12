<?php require_once('header.php'); ?>
<?php require_once('../includes/file_optimizer.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;
	
	// Check if new image is being uploaded
	$uploadingNewImage = !empty($_POST['cropped_image']);
	
	if($uploadingNewImage) {
		// Process the cropped image
		$croppedImage = $_POST['cropped_image'];
		
		// Remove the data:image/png;base64, part
		$imageData = explode(',', $croppedImage);
		$imageData = base64_decode($imageData[1]);
		
		// Create image from string
		$image = imagecreatefromstring($imageData);
		
		if($image !== false) {
			// Delete old slider image
			if(file_exists('../assets/uploads/sliders/'.$_POST['current_photo'])) {
				unlink('../assets/uploads/sliders/'.$_POST['current_photo']);
			}
			
			// Create temporary file
			$tempFile = sys_get_temp_dir() . '/slider_temp_' . uniqid() . '.png';
			imagepng($image, $tempFile);
			imagedestroy($image);
			
			// Use FileOptimizer to convert to WebP with maxWidth=1920 to preserve quality
			$uploadDir = '../assets/uploads/sliders/';
			$destPath = $uploadDir . 'slider-' . $_REQUEST['id'] . '.webp';

			$success = FileOptimizer::optimizeImage($tempFile, $destPath, 1920, 85);

			if ($success) {
				$optimizedFilename = 'slider-' . $_REQUEST['id'] . '.webp';
			} else {
				// Fallback to PNG if optimization fails
				$destPathPng = $uploadDir . 'slider-' . $_REQUEST['id'] . '.png';
				if (copy($tempFile, $destPathPng)) {
					$optimizedFilename = 'slider-' . $_REQUEST['id'] . '.png';
				} else {
					$optimizedFilename = false;
				}
			}
			
			// Clean up temp file
			if(file_exists($tempFile)) {
				unlink($tempFile);
			}
			
			if($optimizedFilename) {
				// Update database with new photo and heading
				$statement = $pdo->prepare("UPDATE tbl_slider SET photo=?, heading=? WHERE id=?");
				$statement->execute(array($optimizedFilename,$_POST['heading'],$_REQUEST['id']));
				$success_message = 'Slider updated successfully!';
			} else {
				$error_message .= 'Failed to save slider image<br>';
			}
		} else {
			$error_message .= 'Failed to process cropped image<br>';
		}
	} else {
		// Only update heading
		$statement = $pdo->prepare("UPDATE tbl_slider SET heading=? WHERE id=?");
		$statement->execute(array($_POST['heading'],$_REQUEST['id']));
		$success_message = 'Slider updated successfully!';
	}
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	$statement = $pdo->prepare("SELECT * FROM tbl_slider WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if($total == 0) {
		header('location: logout.php');
		exit;
	}
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<style>
/* Same modern styles as slider-add.php */
.upload-wizard {
	max-width: 900px;
	margin: 0 auto;
}

.existing-slider-section {
	background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
	border-radius: 16px;
	padding: 30px;
	margin-bottom: 30px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.existing-slider-header {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 20px;
	color: #1e293b;
}

.existing-slider-header i {
	font-size: 28px;
	color: #667eea;
}

.existing-slider-header h3 {
	margin: 0;
	font-size: 24px;
	font-weight: 700;
}

.existing-image-box {
	background: #ffffff;
	border-radius: 12px;
	padding: 20px;
	box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
	margin-bottom: 20px;
}

.existing-image-box img {
	max-width: 100%;
	height: auto;
	border-radius: 8px;
	display: block;
	margin: 0 auto;
}

.existing-heading {
	background: #ffffff;
	padding: 15px 20px;
	border-radius: 10px;
	border-left: 4px solid #667eea;
}

.existing-heading strong {
	color: #1e293b;
	display: block;
	margin-bottom: 8px;
}

.existing-heading p {
	margin: 0;
	color: #64748b;
	font-size: 16px;
}

.change-options {
	display: flex;
	gap: 12px;
	flex-wrap: wrap;
}

.info-box {
	background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
	border-left: 4px solid #3b82f6;
	padding: 16px;
	border-radius: 8px;
	margin-bottom: 25px;
}

.info-box-icon {
	display: inline-block;
	margin-right: 10px;
	color: #1e40af;
	font-size: 20px;
}

.info-box-text {
	color: #1e3a8a;
	font-size: 14px;
	line-height: 1.6;
}

.btn-modern {
	padding: 12px 28px;
	border: none;
	border-radius: 8px;
	font-weight: 600;
	font-size: 15px;
	cursor: pointer;
	transition: all 0.3s ease;
	display: inline-flex;
	align-items: center;
	gap: 8px;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-modern:hover {
	transform: translateY(-2px);
	box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.btn-primary-modern {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #ffffff;
}

.btn-success-modern {
	background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
	color: #ffffff;
}

.btn-warning-modern {
	background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
	color: #1e293b;
}

.btn-secondary-modern {
	background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
	color: #1e293b;
}

.form-section {
	background: #ffffff;
	border-radius: 16px;
	padding: 30px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.form-group-modern {
	margin-bottom: 25px;
}

.form-group-modern label {
	display: block;
	font-weight: 600;
	color: #1e293b;
	margin-bottom: 8px;
	font-size: 15px;
}

.form-control-modern {
	width: 100%;
	padding: 14px 18px;
	border: 2px solid #e2e8f0;
	border-radius: 10px;
	font-size: 15px;
	transition: all 0.3s ease;
	background: #f8fafc;
}

.form-control-modern:focus {
	outline: none;
	border-color: #667eea;
	background: #ffffff;
	box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.action-buttons {
	display: flex;
	gap: 12px;
	justify-content: flex-end;
	margin-top: 30px;
	flex-wrap: wrap;
}

/* Crop Section (same as add page) */
.crop-container {
	background: #ffffff;
	border-radius: 16px;
	padding: 30px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
	margin-bottom: 20px;
	display: none;
}

.crop-container.active {
	display: block;
	animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
	from {
		opacity: 0;
		transform: translateY(20px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.crop-preview-box {
	background: #000000;
	border-radius: 12px;
	overflow: hidden;
	margin-bottom: 20px;
	box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
}

#image-preview {
	max-width: 100%;
	display: block;
	margin: 0 auto;
}

.crop-controls {
	display: flex;
	gap: 12px;
	justify-content: center;
	flex-wrap: wrap;
}

.preview-container {
	background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
	border: 3px solid #4ade80;
	border-radius: 16px;
	padding: 30px;
	margin-bottom: 20px;
	display: none;
}

.preview-container.active {
	display: block;
	animation: fadeInUp 0.4s ease;
}

.preview-header {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 20px;
	color: #166534;
}

.preview-header i {
	font-size: 28px;
}

.preview-header h3 {
	margin: 0;
	font-size: 24px;
	font-weight: 700;
}

.preview-image-box {
	background: #ffffff;
	border-radius: 12px;
	padding: 20px;
	box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

#crop-result-preview {
	max-width: 100%;
	height: auto;
	border-radius: 8px;
	display: block;
	margin: 0 auto;
}

.preview-info {
	text-align: center;
	margin-top: 15px;
	padding: 12px;
	background: #f0fdf4;
	border-radius: 8px;
}

.preview-info strong {
	color: #166534;
	font-size: 16px;
}

#photo-input {
	display: none;
}

@media (max-width: 768px) {
	.change-options,
	.action-buttons {
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
		<h1>Edit Slider</h1>
	</div>
	<div class="content-header-right">
		<a href="slider.php" class="btn btn-primary btn-sm">
			<i class="fa fa-list"></i> View All Sliders
		</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_slider WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $row) {
	$photo = $row['photo'];
	$heading = $row['heading'];
}
?>

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
				<!-- Existing Slider -->
				<div class="existing-slider-section" id="existing-section">
					<div class="existing-slider-header">
						<i class="fa fa-image"></i>
						<h3>Current Slider</h3>
					</div>

					<div class="existing-image-box">
						<img src="../assets/uploads/sliders/<?php echo $photo; ?>" alt="Current Slider">
					</div>

					<?php if(!empty($heading)): ?>
					<div class="existing-heading">
						<strong>Current Heading:</strong>
						<p>"<?php echo htmlspecialchars($heading); ?>"</p>
					</div>
					<?php endif; ?>
				</div>

				<!-- Main Form -->
				<form id="slider-form" method="post" action="">
					<input type="hidden" name="current_photo" value="<?php echo $photo; ?>">
					<input type="hidden" name="cropped_image" id="cropped-image-data">

					<!-- Update Options -->
					<div class="form-section" id="update-section">
						<h3 style="margin: 0 0 20px 0; color: #1e293b;">
							<i class="fa fa-edit"></i> Update Slider
						</h3>

						<div class="info-box">
							<i class="fa fa-info-circle info-box-icon"></i>
							<span class="info-box-text">
								You can update just the heading, or upload a new image (1920 x 280 pixels), or both!
							</span>
						</div>

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
								value="<?php echo htmlspecialchars($heading); ?>"
							>
						</div>

						<div class="form-group-modern">
							<label>
								<i class="fa fa-image"></i> Change Image (Optional)
							</label>
							<div class="change-options">
								<button type="button" class="btn-modern btn-primary-modern" id="change-image-btn">
									<i class="fa fa-upload"></i> Upload New Image
								</button>
								<button type="submit" class="btn-modern btn-success-modern" name="form1">
									<i class="fa fa-save"></i> Save Changes
								</button>
							</div>
							<input type="file" id="photo-input" accept="image/jpeg,image/jpg,image/png,image/gif">
						</div>
					</div>

					<!-- Crop Section (hidden by default) -->
					<div class="crop-container" id="crop-section">
						<h3 style="margin: 0 0 20px 0; color: #1e293b;">
							<i class="fa fa-crop"></i> Adjust Your New Image
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
							<button type="button" class="btn-modern btn-secondary-modern" id="cancel-upload-btn">
								<i class="fa fa-times"></i> Cancel
							</button>
						</div>
					</div>

					<!-- Preview Section -->
					<div class="preview-container" id="preview-section">
						<div class="preview-header">
							<i class="fa fa-check-circle"></i>
							<h3>New Image Ready!</h3>
						</div>
						<div class="preview-image-box">
							<img id="crop-result-preview" src="" alt="Cropped Preview">
						</div>
						<div class="preview-info">
							<strong>1920 x 280 pixels</strong> • Ready to save
						</div>
						<div class="action-buttons">
							<button type="button" class="btn-modern btn-secondary-modern" id="back-to-crop-btn">
								<i class="fa fa-arrow-left"></i> Adjust Crop
							</button>
							<button type="submit" class="btn-modern btn-primary-modern" name="form1">
								<i class="fa fa-save"></i> Save Changes
							</button>
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

const photoInput = document.getElementById('photo-input');
const changeImageBtn = document.getElementById('change-image-btn');
const cancelUploadBtn = document.getElementById('cancel-upload-btn');
const imagePreview = document.getElementById('image-preview');
const cropBtn = document.getElementById('crop-btn');
const resetBtn = document.getElementById('reset-btn');
const backToCropBtn = document.getElementById('back-to-crop-btn');
const cropResultPreview = document.getElementById('crop-result-preview');
const croppedImageInput = document.getElementById('cropped-image-data');
const cropSection = document.getElementById('crop-section');
const previewSection = document.getElementById('preview-section');
const updateSection = document.getElementById('update-section');
const existingSection = document.getElementById('existing-section');

// Change image button
changeImageBtn.addEventListener('click', function() {
	photoInput.click();
});

// File selection
photoInput.addEventListener('change', function(e) {
	const file = e.target.files[0];
	if(file) {
		// Validate file
		const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
		if(!validTypes.includes(file.type)) {
			alert('❌ Please select a valid image file (JPG, PNG, or GIF)');
			photoInput.value = '';
			return;
		}

		if(file.size > 5 * 1024 * 1024) {
			alert('❌ File size must be less than 5MB');
			photoInput.value = '';
			return;
		}

		// Load image for cropping
		const reader = new FileReader();
		reader.onload = function(event) {
			imagePreview.src = event.target.result;
			
			// Hide other sections
			updateSection.style.display = 'none';
			existingSection.style.display = 'none';
			previewSection.classList.remove('active');
			cropSection.classList.add('active');
			
			// Destroy existing cropper
			if(cropper) {
				cropper.destroy();
			}

			// Initialize cropper
			setTimeout(() => {
				cropper = new Cropper(imagePreview, {
					aspectRatio: 1920 / 280, // Updated to 280px
					viewMode: 1,
					autoCropArea: 1,
					responsive: true,
					guides: true,
					center: true,
					highlight: true,
					cropBoxResizable: false,
					dragMode: 'move',
					background: false
				});
			}, 100);
		};
		reader.readAsDataURL (file);
	}
});

// Crop button
cropBtn.addEventListener('click', function() {
	if(cropper) {
		const canvas = cropper.getCroppedCanvas({
			width: 1920,
			height: 280, // Updated to 280px
			imageSmoothingEnabled: true,
			imageSmoothingQuality: 'high'
		});

		const croppedImageData = canvas.toDataURL('image/png', 1.0);
		croppedImageInput.value = croppedImageData;
		cropResultPreview.src = croppedImageData;

		cropSection.classList.remove('active');
		previewSection.classList.add('active');
	}
});

// Reset button
resetBtn.addEventListener('click', function() {
	if(cropper) {
		cropper.reset();
	}
});

// Cancel button
cancelUploadBtn.addEventListener('click', function() {
	photoInput.value = '';
	croppedImageInput.value = '';
	
	if(cropper) {
		cropper.destroy();
		cropper = null;
	}
	
	cropSection.classList.remove('active');
	previewSection.classList.remove('active');
	updateSection.style.display = 'block';
	existingSection.style.display = 'block';
});

// Back to crop
backToCropBtn.addEventListener('click', function() {
	previewSection.classList.remove('active');
	cropSection.classList.add('active');
});

// Form validation
document.getElementById('slider-form').addEventListener('submit', function(e) {
	if(photoInput.files.length > 0 && !croppedImageInput.value) {
		e.preventDefault();
		alert('⚠️ Please crop the image before saving!');
		return false;
	}
});
</script>

<?php require_once('footer.php'); ?>