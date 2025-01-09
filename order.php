<?php
if (isset($_GET['update']) && $_GET['update'] == 'success') {
    echo '<p class="success-message">Address updated successfully!</p>';
}
?>
<style>
    .success-message {
    color: green;
    font-weight: bold;
    margin: 10px 0;
}

</style>