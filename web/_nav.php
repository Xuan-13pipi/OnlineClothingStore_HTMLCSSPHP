</head>
<body>
    <header>
        <a href="/"><img src="../images/Logo.png" alt="Logo" class="logo"></a>
        <nav>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="/page/admin_dashboard.php"><img src="../images/images_nav/admin-dashboard.svg" alt="admin-btn" height="28" width="28"></a>
                <a href="../page/profile_page.php"><img src="../images/images_nav/user.svg" alt="User" height="28" width="28"></a>
                <a href="/page/cart.php"><img src="../images/images_nav/shopping-cart.svg" alt="Cart" height="28" width="28"></a>
            <?php else: ?>
                <a href="/page/profile_page.php"><img src="../images/images_nav/user.svg" alt="User" height="28" width="28"></a>
                <a href="/page/cart.php"><img src="../images/images_nav/shopping-cart.svg" alt="Cart" height="28" width="28"></a>
            <?php endif; ?>
        </nav>
    </header>
    <main>