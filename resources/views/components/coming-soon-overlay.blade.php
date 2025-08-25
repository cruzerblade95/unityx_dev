<div id="coming-soon-overlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center overlay-backdrop" style="z-index: 10; pointer-events: auto;">
  <div class="overlay-content text-center text-white p-4 p-md-5">
    <div class="overlay-icon mb-3 mb-md-4">
      <i class="fas fa-rocket fa-3x fa-sm-2x text-warning"></i>
    </div>
    <h1 class="overlay-title fw-bold mb-3 mb-md-4">Delta Test!</h1>
    <p class="overlay-subtitle mb-4 mb-md-5">This feature is currently under development</p>
    <div class="overlay-animation">
      <div class="spinner-border text-warning" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
  </div>
</div>

<style>
/* Responsive Coming Soon Overlay */
#coming-soon-overlay {
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  transition: all 0.3s ease;
}

.overlay-content {
  max-width: 90%;
  animation: overlayFadeIn 0.5s ease-out;
}

.overlay-title {
  font-size: clamp(1.5rem, 5vw, 3.5rem);
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  background: linear-gradient(45deg, #fff, #ffc107);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.overlay-subtitle {
  font-size: clamp(0.9rem, 2.5vw, 1.2rem);
  color: rgba(255, 255, 255, 0.8);
  max-width: 600px;
  margin: 0 auto;
}

.overlay-icon {
  animation: iconBounce 2s ease-in-out infinite;
}

.overlay-icon i {
  font-size: clamp(2rem, 6vw, 4rem) !important;
  filter: drop-shadow(0 4px 8px rgba(255, 193, 7, 0.3));
}

.overlay-animation {
  animation: pulseGlow 2s ease-in-out infinite alternate;
}

/* Responsive breakpoints */
@media (max-width: 576px) {
  .overlay-content {
    padding: 2rem 1rem !important;
  }
  
  .overlay-title {
    margin-bottom: 1rem !important;
  }
  
  .overlay-subtitle {
    margin-bottom: 2rem !important;
    font-size: 0.95rem;
  }
  
  .overlay-icon {
    margin-bottom: 1.5rem !important;
  }
}

@media (min-width: 577px) and (max-width: 768px) {
  .overlay-content {
    padding: 2.5rem 1.5rem !important;
  }
}

@media (min-width: 769px) and (max-width: 992px) {
  .overlay-content {
    padding: 3rem 2rem !important;
  }
}

@media (min-width: 993px) {
  .overlay-content {
    padding: 4rem 2.5rem !important;
  }
}

/* Dark theme support */
[data-bs-theme="dark"] #coming-soon-overlay {
  background: rgba(0, 0, 0, 0.9);
}

[data-bs-theme="dark"] .overlay-subtitle {
  color: rgba(255, 255, 255, 0.7);
}

/* Animations */
@keyframes overlayFadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

@keyframes iconBounce {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-10px);
  }
  60% {
    transform: translateY(-5px);
  }
}

@keyframes pulseGlow {
  from {
    transform: scale(1);
    opacity: 1;
  }
  to {
    transform: scale(1.05);
    opacity: 0.8;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  #coming-soon-overlay {
    background: rgba(0, 0, 0, 0.95);
  }
  
  .overlay-title {
    background: none;
    -webkit-text-fill-color: white;
    color: white;
  }
  
  .overlay-subtitle {
    color: rgba(255, 255, 255, 0.9);
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .overlay-content,
  .overlay-icon,
  .overlay-animation {
    animation: none;
  }
  
  #coming-soon-overlay {
    transition: none;
  }
}

/* Print styles */
@media print {
  #coming-soon-overlay {
    display: none !important;
  }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
  .overlay-content {
    padding: 2rem 1rem;
  }
  
  .overlay-icon i {
    font-size: 3rem !important;
  }
}

/* Landscape orientation on mobile */
@media (max-height: 500px) and (orientation: landscape) {
  .overlay-content {
    padding: 1rem !important;
  }
  
  .overlay-icon {
    margin-bottom: 0.5rem !important;
  }
  
  .overlay-title {
    margin-bottom: 0.5rem !important;
    font-size: 2rem !important;
  }
  
  .overlay-subtitle {
    margin-bottom: 1rem !important;
    font-size: 0.9rem;
  }
  
  .overlay-icon i {
    font-size: 2rem !important;
  }
}

/* Extra large screens */
@media (min-width: 1400px) {
  .overlay-content {
    max-width: 70%;
  }
}

/* Ultra-wide screens */
@media (min-width: 1920px) {
  .overlay-content {
    max-width: 50%;
  }
}
</style>
