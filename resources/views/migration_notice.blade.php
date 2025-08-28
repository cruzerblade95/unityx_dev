<!DOCTYPE html>
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Website Migrated - UnityX</title>
  <meta name="description" content="UnityX has migrated to a new domain. Please visit our new website." />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap" rel="stylesheet" />

  <!-- Icons -->
  <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/core.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/rtl/theme-default.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />

  <!-- Page CSS -->
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-misc.css') }}" />

  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <!-- Custom styles for migration notice -->
  <style>
    .migration-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .migration-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      max-width: 600px;
      width: 100%;
      text-align: center;
      padding: 60px 40px;
      position: relative;
      overflow: hidden;
    }

    .migration-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .migration-icon {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      width: 120px;
      height: 120px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 30px;
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .migration-icon i {
      font-size: 60px;
      color: white;
    }

    .migration-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2d3748;
      margin-bottom: 20px;
      line-height: 1.2;
    }

    .migration-subtitle {
      font-size: 1.2rem;
      color: #4a5568;
      margin-bottom: 30px;
      line-height: 1.5;
    }

    .new-domain {
      background: #f7fafc;
      border: 2px dashed #e2e8f0;
      border-radius: 12px;
      padding: 20px;
      margin: 30px 0;
      font-family: 'Courier New', monospace;
      font-size: 1.3rem;
      font-weight: 600;
      color: #2d3748;
      position: relative;
    }

    .new-domain::before {
      content: '🌐';
      position: absolute;
      top: -10px;
      left: 20px;
      background: white;
      padding: 0 10px;
      font-size: 1.5rem;
    }

    .redirect-btn {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 18px 40px;
      border-radius: 50px;
      font-size: 1.1rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 12px;
      transition: all 0.3s ease;
      box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
      margin-top: 20px;
    }

    .redirect-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
      color: white;
      text-decoration: none;
    }

    .redirect-btn i {
      font-size: 1.2rem;
    }

    .features-list {
      text-align: left;
      margin: 30px 0;
      padding: 0;
      list-style: none;
    }

    .features-list li {
      padding: 8px 0;
      color: #4a5568;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .features-list li i {
      color: #48bb78;
      font-size: 1.1rem;
      width: 20px;
    }

    .footer-text {
      margin-top: 40px;
      color: #718096;
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .countdown {
      background: #fff5f5;
      border: 1px solid #fed7d7;
      border-radius: 8px;
      padding: 15px;
      margin: 20px 0;
      color: #c53030;
      font-weight: 600;
    }

    @media (max-width: 768px) {
      .migration-card {
        padding: 40px 20px;
        margin: 20px;
      }

      .migration-title {
        font-size: 2rem;
      }

      .migration-subtitle {
        font-size: 1.1rem;
      }

      .new-domain {
        font-size: 1.1rem;
      }
    }

    /* Animation for the icon */
    @keyframes pulse {
      0% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.05);
      }
      100% {
        transform: scale(1);
      }
    }

    .migration-icon {
      animation: pulse 2s infinite;
    }
  </style>
</head>

<body>
  <div class="migration-container">
    <div class="migration-card">
      <div class="migration-icon">
        <i class="fas fa-rocket"></i>
      </div>

      <h1 class="migration-title">We've Moved!</h1>

      <p class="migration-subtitle">
        Our website has been migrated to a new and improved platform.
        Please visit our new domain to continue your journey with UnityX.
      </p>

      <div class="new-domain">
        https://unityx.pro
      </div>

      <div class="countdown">
        <i class="fas fa-clock me-2"></i>
        You will be automatically redirected in <span id="countdown">10</span> seconds
      </div>

      <a href="https://unityx.pro" class="redirect-btn" id="redirectBtn">
        <i class="fas fa-external-link-alt"></i>
        Visit New Website
      </a>

      <div class="row text-center mt-4">
        <div class="col-12">
          <h6 class="mb-3" style="color: #4a5568;">What's New:</h6>
          <ul class="features-list">
            <li>
              <i class="fas fa-check-circle"></i>
              Enhanced security and performance
            </li>
            <li>
              <i class="fas fa-check-circle"></i>
              Improved user interface and experience
            </li>
            <li>
              <i class="fas fa-check-circle"></i>
              Better mobile responsiveness
            </li>
            <li>
              <i class="fas fa-check-circle"></i>
              New features and functionalities
            </li>
          </ul>
        </div>
      </div>

      <div class="footer-text">
        <p>
          <strong>Important:</strong> Please update your bookmarks to point to our new domain.
          All your account data and settings have been safely migrated.
        </p>
        <p>
          If you have any questions or concerns, please contact our support team.
        </p>
      </div>
    </div>
  </div>

  <!-- Auto-redirect script -->
  <script>
    let countdown = 10;
    const countdownElement = document.getElementById('countdown');
    const redirectBtn = document.getElementById('redirectBtn');

    // Update countdown every second
    const timer = setInterval(() => {
      countdown--;
      countdownElement.textContent = countdown;

      if (countdown <= 0) {
        clearInterval(timer);
        window.location.href = 'https://unityx.pro';
      }
    }, 1000);

    // Allow manual redirect
    redirectBtn.addEventListener('click', (e) => {
      e.preventDefault();
      clearInterval(timer);
      window.location.href = 'https://unityx.pro';
    });

    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
      // Add click effect to the card
      const card = document.querySelector('.migration-card');
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
      });

      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
      });
    });
  </script>
</body>
</html>
