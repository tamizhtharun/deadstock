<?php
// Footer configuration
$footerConfig = [
    'company' => [
        'name' => 'StyleVibe',
        'tagline' => 'Elevating Your Shopping Experience',
        'description' => 'Discover the perfect blend of style and convenience with our curated collection of premium products.',
        'social' => [
            'facebook' => 'https://facebook.com/stylevibe',
            'instagram' => 'https://instagram.com/stylevibe',
            'twitter' => 'https://twitter.com/stylevibe',
            'pinterest' => 'https://pinterest.com/stylevibe'
        ]
    ],
    'newsletter' => [
        'title' => 'Join Our Community',
        'description' => 'Subscribe for exclusive offers, new arrivals, and style inspiration.'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .footer {
            background: linear-gradient(180deg, #f8f8f8 0%, #ffffff 100%);
            padding: 4rem 0 2rem;
            color: #333;
            font-family: 'Inter', sans-serif;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .footer-brand {
            max-width: 300px;
        }

        .footer-brand h2 {
            font-size: 1.8rem;
            margin: 0 0 1rem;
            color: #1a1a1a;
        }

        .footer-brand p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            background: #f0f0f0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .footer-links h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: #1a1a1a;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #1a1a1a;
        }

        .newsletter-form {
            margin-top: 1.5rem;
        }

        .newsletter-form input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .newsletter-form button {
            width: 100%;
            padding: 0.8rem;
            background: #1a1a1a;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .newsletter-form button:hover {
            background: #333;
        }

        .footer-bottom {
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .footer {
                padding: 3rem 0 1.5rem;
            }

            .footer-grid {
                gap: 2rem;
            }

            .footer-brand {
                max-width: 100%;
            }

            .footer-bottom {
                margin-top: 3rem;
            }
        }
    </style>
</head>
<body>
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Brand Section -->
                <div class="footer-brand">
                    <h2><?php echo $footerConfig['company']['name']; ?></h2>
                    <p><?php echo $footerConfig['company']['description']; ?></p>
                    <div class="social-links">
                        <?php foreach ($footerConfig['company']['social'] as $platform => $url): ?>
                            <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer">
                                <img src="icons/<?php echo $platform; ?>.svg" alt="<?php echo ucfirst($platform); ?>" width="20" height="20">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Shop Links -->
                <div class="footer-links">
                    <h3>Shop</h3>
                    <ul>
                        <li><a href="/new-arrivals">New Arrivals</a></li>
                        <li><a href="/bestsellers">Bestsellers</a></li>
                        <li><a href="/categories">Shop by Category</a></li>
                        <li><a href="/sale">Sale</a></li>
                        <li><a href="/gift-cards">Gift Cards</a></li>
                    </ul>
                </div>

                <!-- Help Links -->
                <div class="footer-links">
                    <h3>Help</h3>
                    <ul>
                        <li><a href="/shipping">Shipping Information</a></li>
                        <li><a href="/returns">Returns & Exchanges</a></li>
                        <li><a href="/size-guide">Size Guide</a></li>
                        <li><a href="/contact">Contact Us</a></li>
                        <li><a href="/faq">FAQ</a></li>
                    </ul>
                </div>

                <!-- Newsletter Section -->
                <div class="footer-links">
                    <h3><?php echo $footerConfig['newsletter']['title']; ?></h3>
                    <p><?php echo $footerConfig['newsletter']['description']; ?></p>
                    <form class="newsletter-form" action="/subscribe" method="POST">
                        <input type="email" name="email" placeholder="Enter your email" required>
                        <button type="submit">Subscribe</button>
                    </form>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $footerConfig['company']['name']; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>