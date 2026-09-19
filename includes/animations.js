// Sistem Tagihan Air - Animasi Elegan
document.addEventListener('DOMContentLoaded', function() {
  
  // Page load animation
  document.body.classList.add('fade-in');
  
  // Scroll reveal animation
  var observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
  var scrollObserver = new IntersectionObserver(function(entries) {
    var i;
    for (i = 0; i < entries.length; i++) {
      if (entries[i].isIntersecting) {
        entries[i].target.classList.add('show');
        var delay = parseInt(entries[i].target.dataset.stagger || 0);
        setTimeout(function() { entries[i].target.classList.add('active'); }, delay);
      }
    }
  }, observerOptions);
  
  // Initialize scroll animations
  var scrollElements = document.querySelectorAll('.scrollFadeIn');
  var i, j;
  for (i = 0; i < scrollElements.length; i++) {
    scrollElements[i].dataset.stagger = i * 100;
    scrollElements[i].style.animationDelay = (i * 0.1) + 's';
    scrollObserver.observe(scrollElements[i]);
  }
  
  // Back to top button
  var backToTopBtn = document.createElement('button');
  backToTopBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
  backToTopBtn.className = 'btn btn-primary back-to-top';
  backToTopBtn.style.cssText = 'position: fixed;bottom: 30px;right: 30px;width: 50px;height: 50px;border-radius: 50%;box-shadow: 0 4px 20px rgba(14,165,233,0.4);opacity: 0;visibility: hidden;transition: all 0.3s ease;z-index: 1000;';
  document.body.appendChild(backToTopBtn);
  
  window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
      backToTopBtn.style.opacity = '1';
      backToTopBtn.style.visibility = 'visible';
    } else {
      backToTopBtn.style.opacity = '0';
      backToTopBtn.style.visibility = 'hidden';
    }
  });
  
  backToTopBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
  
  // Auto-hide alerts
  var alerts = document.querySelectorAll('.alert');
  for (i = 0; i < alerts.length; i++) {
    (function(idx) {
      setTimeout(function() {
        alerts[idx].classList.add('fade-out');
        setTimeout(function() {
          if (alerts[idx].parentNode) { alerts[idx].parentNode.removeChild(alerts[idx]); }
        }, 600);
      }, 5000);
    })(i);
  }
});