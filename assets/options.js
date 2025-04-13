document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery) {
      jQuery('.color-picker').wpColorPicker();
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.aof-tab');
    const contents = document.querySelectorAll('.aof-tab-content');
  
    if (!tabs.length) return;
  
    // Activate the first tab by default
    tabs[0].classList.add('active');
    contents[0].classList.add('active');
  
    tabs.forEach(tab => {
      tab.addEventListener('click', function () {
        const targetId = this.getAttribute('data-tab');
  
        // Remove active class from all tabs and contents
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));
  
        // Activate current tab and related content
        this.classList.add('active');
        const content = document.getElementById(targetId);
        if (content) content.classList.add('active');
      });
    });
  });
  