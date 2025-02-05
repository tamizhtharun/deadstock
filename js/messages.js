// Show message function
function showMessage(text, type = 'success') {
  const container = document.getElementById('message-container');
  
  const message = document.createElement('div');
  message.className = `message-box ${type}`;
  message.innerHTML = text;

  container.prepend(message);

  // Auto-remove after animation
  setTimeout(() => {
      message.remove();
  }, 4500);
}

// Optional: Close on click
document.addEventListener('click', (e) => {
  if (e.target.closest('.message-box')) {
      e.target.closest('.message-box').remove();
  }
});