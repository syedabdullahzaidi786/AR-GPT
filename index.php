<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Assistant - Your Smart Conversation Partner</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1d4ed8;
            --background-color: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --gradient-start: #2563eb;
            --gradient-end: #1d4ed8;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Inter', 'Google Sans', 'Roboto', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-primary);
            gap: 0.75rem;
        }

        .navbar-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .navbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin: 0;
            padding: 0;
            list-style: none;
            flex-direction: row;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            position: relative;
            white-space: nowrap;
        }

        .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.05);
            transform: translateY(-1px);
        }

        .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(37, 99, 235, 0.1);
            font-weight: 600;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 3px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            border-radius: 3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
            white-space: nowrap;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
        }

        .hamburger-menu {
            display: none;
            cursor: pointer;
            padding: 0.5rem;
            border: none;
            background: none;
        }

        .hamburger-menu span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: var(--text-primary);
            margin: 5px 0;
            transition: all 0.3s ease;
        }

        .main-content {
            margin-top: 80px;
            padding: 2rem;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.5rem 1rem;
            }

            .hamburger-menu {
                display: block;
            }

            .navbar-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                padding: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                flex-direction: column;
                gap: 0.5rem;
            }

            .navbar-nav.active {
                display: flex;
            }

            .nav-link {
                display: block;
                padding: 0.75rem;
                text-align: center;
                width: 100%;
            }

            .nav-link.active::after {
                display: none;
            }

            .btn-primary {
                width: 100%;
                text-align: center;
            }

            .main-content {
                margin-top: 80px;
            }
        }

        .hero-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 6rem 0;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-subtitle {
            font-size: 1.35rem;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            max-width: 700px;
            line-height: 1.8;
            font-weight: 500;
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }

        .stat-info h4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            line-height: 1.2;
        }

        .stat-info p {
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .feature-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
            color: white;
            font-size: 1.75rem;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }

        .feature-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
            line-height: 1.3;
        }

        .feature-description {
            color: var(--text-secondary);
            font-size: 1.15rem;
            line-height: 1.7;
        }

        .cta-button {
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 30px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cta-button:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .login-button {
            background: white;
            color: var(--primary-color);
            padding: 1rem 2rem;
            border-radius: 30px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 2px solid var(--primary-color);
        }

        .login-button:hover {
            background: #f8f9fa;
            color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .signup-button {
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 30px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .signup-button:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .demo-video {
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            aspect-ratio: 16/9;
            width: 100%;
            max-width: 800px;
            margin: 2rem auto;
        }

        .demo-video iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            background: var(--primary-color);
            opacity: 0.1;
            border-radius: 50%;
        }

        .shape-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
        }

        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
        }

        .shape-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            right: 10%;
        }

        .hero-image {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 400px;
            background-image: url('./images/cover.png');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .hero-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(26, 115, 232, 0.2), rgba(21, 88, 179, 0.2));
            border-radius: 20px;
        }

        .feature-image {
            width: 100%;
            height: 200px;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .feature-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-image img {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 3rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }

            .stat-item {
                width: 100%;
            }

            .feature-card {
                padding: 2rem;
            }

            .feature-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .auth-buttons {
                flex-direction: column;
                gap: 0.75rem;
            }

            .login-button, .signup-button {
                padding: 0.875rem 1.75rem;
                font-size: 0.95rem;
            }

            .hero-image {
                min-height: 300px;
                margin-top: 2rem;
            }

            .feature-image {
                height: 160px;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .feature-card {
                padding: 1.5rem;
            }

            .auth-buttons {
                gap: 0.5rem;
            }

            .login-button, .signup-button {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }

            .hero-image {
                min-height: 250px;
            }

            .feature-image {
                height: 140px;
            }
        }

        /* Plans Section Styles */
        .plans-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }

        .plans-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .plans-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .plans-title p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .plan-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .plan-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .plan-card.popular {
            border: 2px solid var(--primary-color);
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }

        .plan-card.popular::before {
            content: 'Most Popular';
            position: absolute;
            top: 1rem;
            right: -2rem;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            padding: 0.5rem 3rem;
            transform: rotate(45deg);
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .plan-name {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .plan-price {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            line-height: 1;
        }

        .plan-price span {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 2rem 0;
        }

        .plan-features li {
            padding: 0.75rem 0;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
        }

        .plan-features li i {
            color: #10b981;
            font-size: 1.2rem;
        }

        .plan-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .plan-button.primary {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }

        .plan-button.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.3);
        }

        .plan-button.secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .plan-button.secondary:hover {
            background: #f8fafc;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .plans-section {
                padding: 4rem 0;
            }

            .plans-title h2 {
                font-size: 2rem;
            }

            .plan-card {
                padding: 2rem;
            }

            .plan-price {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .plans-section {
                padding: 3rem 0;
            }

            .plans-title h2 {
                font-size: 1.75rem;
            }

            .plan-card {
                padding: 1.5rem;
            }

            .plan-price {
                font-size: 2rem;
            }
        }

        .footer-section {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            padding: 6rem 0 2rem;
            margin-top: 4rem;
            position: relative;
        }

        .footer-wave {
            position: absolute;
            top: -100px;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
        }

        .footer-wave svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 100px;
        }

        .brand-logo {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .footer-brand h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .footer-brand p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            line-height: 1.8;
            color: #94a3b8;
        }

        .social-links {
            display: flex;
            gap: 1.25rem;
        }

        .social-link {
            color: #94a3b8;
            font-size: 1.4rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .social-link:hover {
            color: white;
            transform: translateY(-3px);
        }

        .footer-section h5 {
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .footer-section h5::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--gradient-start), transparent);
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 1rem;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
        }

        .footer-links a:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-contact li {
            color: #94a3b8;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .contact-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
        }

        .contact-info span {
            font-size: 0.9rem;
            color: white;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .contact-info a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 1.1rem;
        }

        .contact-info a:hover {
            color: white;
        }

        .newsletter-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 2.5rem;
            border-radius: 16px;
            margin: 3rem 0;
            backdrop-filter: blur(10px);
        }

        .newsletter-section h5 {
            margin-bottom: 0.75rem;
            font-size: 1.4rem;
        }

        .newsletter-section p {
            color: #94a3b8;
            margin: 0;
            font-size: 1.1rem;
        }

        .newsletter-form .input-group {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 6px;
            margin-top: 1.5rem;
        }

        .newsletter-form .form-control {
            background: transparent;
            border: none;
            color: white;
            padding: 0.875rem 1.5rem;
            font-size: 1.1rem;
        }

        .newsletter-form .form-control::placeholder {
            color: #94a3b8;
        }

        .newsletter-form .btn {
            border-radius: 10px;
            padding: 0.875rem 1.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
        }

        .footer-divider {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 3rem 0;
        }

        .copyright {
            color: #94a3b8;
            margin: 0;
            font-size: 1.1rem;
        }

        .footer-bottom-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: flex-end;
            gap: 2rem;
        }

        .footer-bottom-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.3s ease;
            font-size: 1.1rem;
        }

        .footer-bottom-links a:hover {
            color: white;
        }

        @media (max-width: 768px) {
            .footer-section {
                padding: 4rem 0 1.5rem;
            }

            .footer-wave {
                top: -50px;
            }

            .footer-wave svg {
                height: 50px;
            }

            .newsletter-section {
                text-align: center;
                padding: 2rem;
            }

            .newsletter-form {
                margin-top: 1.5rem;
            }

            .footer-bottom-links {
                justify-content: center;
                margin-top: 1.5rem;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .copyright {
                text-align: center;
                margin-bottom: 1rem;
            }
        }

        @media (max-width: 480px) {
            .footer-section {
                padding: 3rem 0 1rem;
            }

            .footer-wave {
                top: -30px;
            }

            .footer-wave svg {
                height: 30px;
            }

            .footer-brand h3 {
                font-size: 1.75rem;
            }

            .footer-bottom-links {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .newsletter-form .input-group {
                flex-direction: column;
                gap: 1rem;
            }

            .newsletter-form .btn {
                width: 100%;
            }
        }

        /* Modal Styles */
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-label {
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.1);
        }

        .form-check-label {
            color: var(--text-secondary);
        }

        .btn-primary {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 8px;
            background: var(--primary-color);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .modal-body a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .modal-body a:hover {
            color: var(--secondary-color);
        }

        /* Form Validation Styles */
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            color: #dc3545;
            margin-top: 0.25rem;
        }

        /* Loading State */
        .btn-primary.loading {
            position: relative;
            color: transparent;
        }

        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin: -10px 0 0 -10px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: button-loading-spinner 0.8s linear infinite;
        }

        @keyframes button-loading-spinner {
            from {
                transform: rotate(0turn);
            }
            to {
                transform: rotate(1turn);
            }
        }

        /* Add styles for error message */
        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .alert-danger {
            background-color: #fff5f5;
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }
        .alert i {
            font-size: 1.1rem;
        }
        .btn-close {
            padding: 0.5rem;
            margin: -0.5rem -0.5rem -0.5rem auto;
        }

        .plan-description {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            min-height: 3.2rem;
        }

        .plan-card {
            display: flex;
            flex-direction: column;
        }

        .plan-card .plan-button {
            margin-top: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <img src="./images/logo.png" alt="AR GPT Logo" class="navbar-logo">
                <span class="navbar-title">AR GPT</span>
            </a>
            <button class="hamburger-menu" onclick="toggleMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="navbar-nav" id="navbarNav">
                <li><a href="index.php" class="nav-link active">Home</a></li>
                <li><a href="#" class="nav-link">Features</a></li>
                <li><a href="plans.php" class="nav-link">Pricing</a></li>
                <li><a href="#" class="nav-link">About</a></li>
                <li><a href="login.php" class="btn-primary">Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
    <div class="hero-section">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                        <h1 class="hero-title">AR GPT - Your AI Assistant</h1>
                    <p class="hero-subtitle">
                            Experience the future of artificial intelligence with AR GPT. Our advanced AI assistant combines cutting-edge technology with natural language understanding to provide intelligent, context-aware conversations and solutions.
                        </p>
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>10K+</h4>
                                    <p>Active Users</p>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>1M+</h4>
                                    <p>Conversations</p>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>4.9</h4>
                                    <p>User Rating</p>
                                </div>
                            </div>
                        </div>
                    <div class="auth-buttons">
                            <a href="login.php" class="login-button">
                            <i class="fas fa-sign-in-alt"></i>
                            Login
                        </a>
                            <a href="signup.php" class="signup-button">
                            <i class="fas fa-user-plus"></i>
                            Sign Up
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <!-- Image is set via CSS background-image -->
                    </div>
                </div>
            </div>

            <div class="row mt-5 pt-5">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-image">
                                <img src="https://assets.everspringpartners.com/dims4/default/fb976e9/2147483647/strip/true/crop/686x360+171+0/resize/1200x630!/quality/90/?url=http%3A%2F%2Feverspring-brightspot.s3.us-east-1.amazonaws.com%2Fdf%2Fee%2F106592af4d508f76b29662e456db%2Fadvanced-ai.jpg" alt="AI Technology">
                        </div>
                        <div class="feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                            <h3 class="feature-title">Advanced AI Technology</h3>
                        <p class="feature-description">
                                Experience cutting-edge AI technology powered by state-of-the-art language models. Our system delivers intelligent, context-aware responses for natural and meaningful conversations.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-image">
                                <img src="https://www.scylladb.com/wp-content/uploads/real-time-data-diagram.png" alt="Real-time Processing">
                        </div>
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                            <h3 class="feature-title">Real-time Processing</h3>
                        <p class="feature-description">
                                Get instant responses with our optimized real-time processing system. Experience lightning-fast interactions and seamless conversations without any delays.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-image">
                                <img src="https://saasaro.com/assets/saasaro_grade1.webp" alt="Enterprise Security">
                        </div>
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                            <h3 class="feature-title">Enterprise-Grade Security</h3>
                        <p class="feature-description">
                                Your data is protected with military-grade encryption and advanced security measures. We ensure complete privacy and data protection for all your conversations.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans Section -->
    <section class="plans-section">
        <div class="container">
            <div class="plans-title">
                <h2>Choose Your Plan</h2>
                <p>Select the perfect plan that suits your needs. All plans include our core features.</p>
            </div>
            <div class="row">
                    <?php
                    // Database connection
                    $host = 'localhost';
                    $dbname = 'ar_bot';
                    $username = 'root';
                    $password = '';
                    
                    try {
                        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        $stmt = $pdo->query("SELECT * FROM plans ORDER BY price ASC");
                        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($plans as $plan) {
                            $features = json_decode($plan['features'] ?? '[]', true);
                            $duration = $plan['duration'];
                            $durationText = $duration == 30 ? 'month' : ($duration == 365 ? 'year' : $duration . ' days');
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="plan-card">
                                    <h3 class="plan-name"><?php echo htmlspecialchars($plan['name']); ?></h3>
                                    <div class="plan-price">
                                        $<?php echo number_format($plan['price'], 2); ?> 
                                        <span>/<?php echo $durationText; ?></span>
                                    </div>
                                    <p class="plan-description">
                                        <?php echo htmlspecialchars($plan['description']); ?>
                                    </p>
                                    <ul class="plan-features">
                                        <?php 
                                        if (is_array($features)) {
                                            foreach ($features as $feature) { 
                                        ?>
                                            <li>
                                                <i class="fas fa-check"></i>
                                                <?php echo htmlspecialchars($feature); ?>
                                            </li>
                                        <?php 
                                            }
                                        }
                                        ?>
                                    </ul>
                                    <a href="signup.php?plan=<?php echo $plan['id']; ?>" 
                                       class="plan-button primary">
                                        Get Started
                                    </a>
                                </div>
                            </div>
                            <?php
                        }
                    } catch (PDOException $e) {
                        // Fallback to default plans if database error occurs
                        ?>
                <div class="col-md-4 mb-4">
                    <div class="plan-card">
                        <h3 class="plan-name">Free</h3>
                        <div class="plan-price">$0 <span>/month</span></div>
                                <p class="plan-description">
                                    Perfect for trying out our basic features
                                </p>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> Basic AI Chat Support</li>
                            <li><i class="fas fa-check"></i> 100 Messages per month</li>
                            <li><i class="fas fa-check"></i> Standard Response Time</li>
                            <li><i class="fas fa-check"></i> Community Support</li>
                        </ul>
                        <a href="signup.php" class="plan-button secondary">Get Started</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="plan-card popular">
                        <h3 class="plan-name">Pro</h3>
                        <div class="plan-price">$19 <span>/month</span></div>
                                <p class="plan-description">
                                    Best for individuals and small teams
                                </p>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> Advanced AI Chat Support</li>
                            <li><i class="fas fa-check"></i> Unlimited Messages</li>
                            <li><i class="fas fa-check"></i> Priority Response Time</li>
                            <li><i class="fas fa-check"></i> Priority Support</li>
                            <li><i class="fas fa-check"></i> Custom AI Training</li>
                        </ul>
                        <a href="signup.php" class="plan-button primary">Get Started</a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="plan-card">
                        <h3 class="plan-name">Enterprise</h3>
                        <div class="plan-price">$49 <span>/month</span></div>
                                <p class="plan-description">
                                    For large organizations with advanced needs
                                </p>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> Premium AI Chat Support</li>
                            <li><i class="fas fa-check"></i> Unlimited Everything</li>
                            <li><i class="fas fa-check"></i> Instant Response Time</li>
                            <li><i class="fas fa-check"></i> 24/7 Dedicated Support</li>
                            <li><i class="fas fa-check"></i> Custom AI Training</li>
                            <li><i class="fas fa-check"></i> API Access</li>
                        </ul>
                        <a href="signup.php" class="plan-button secondary">Contact Sales</a>
                    </div>
                </div>
                        <?php
                    }
                    ?>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer-section">
        <div class="footer-wave">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#1a1a1a" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,149.3C960,160,1056,160,1152,138.7C1248,117,1344,75,1392,53.3L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">
                        <div class="brand-logo">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h3>AR Developers</h3>
                            <p style="color: #ffffff;">Empowering the future with innovative AI solutions. Building tomorrow's technology, today.</p>
                        <div class="social-links">
                            <a href="#" class="social-link" data-tooltip="Facebook"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="social-link" data-tooltip="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link" data-tooltip="LinkedIn"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="social-link" data-tooltip="GitHub"><i class="fab fa-github"></i></a>
                            <a href="#" class="social-link" data-tooltip="Instagram"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="plans.php"><i class="fas fa-chevron-right"></i> Plans</a></li>
                        <li><a href="login.php"><i class="fas fa-chevron-right"></i> Login</a></li>
                        <li><a href="signup.php"><i class="fas fa-chevron-right"></i> Sign Up</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Blog</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5>Features</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-comments"></i> AI Chat Support</a></li>
                        <li><a href="#"><i class="fas fa-brain"></i> Smart Conversations</a></li>
                        <li><a href="#"><i class="fas fa-cogs"></i> Custom Training</a></li>
                        <li><a href="#"><i class="fas fa-code"></i> API Access</a></li>
                        <li><a href="#"><i class="fas fa-chart-line"></i> Analytics</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5>Contact Us</h5>
                    <ul class="footer-contact">
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-info">
                                <span>Email Us</span>
                                <a href="mailto:support@ardevelopers.com">support@ardevelopers.com</a>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-info">
                                <span>Call Us</span>
                                <a href="tel:+1234567890">+1 234 567 890</a>
                            </div>
                        </li>
                        <li>
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-info">
                                <span>Visit Us</span>
                                <a href="#">123 Tech Street, Digital City</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="newsletter-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5>Subscribe to Our Newsletter</h5>
                        <p>Stay updated with our latest features and updates</p>
                    </div>
                    <div class="col-md-6">
                        <form class="newsletter-form">
                            <div class="input-group">
                                <input type="email" class="form-control" placeholder="Enter your email">
                                <button class="btn btn-primary" type="submit">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="row">
                <div class="col-md-6">
                        <p class="copyright">© 2025 AR Developers. All rights reserved.</p>
                </div>
                <div class="col-md-6">
                    <ul class="footer-bottom-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Sitemap</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    </div>

    <script>
        function toggleMenu() {
            const navbarNav = document.getElementById('navbarNav');
            navbarNav.classList.toggle('active');
        }
    </script>
</body>
</html> 