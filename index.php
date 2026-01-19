<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Registration System</title>

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* ===== PAGE LAYOUT (FOOTER FIX) ===== */
html, body {
    height: 100%;
    margin: 0;
}

.page-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.page-content {
    flex: 1;
}

/* ===== HERO ===== */
.hero {
    background: linear-gradient(135deg,#4e54c8,#8f94fb);
    color: white;
    padding: 90px 20px;
    text-align: center;
}

.hero h1 {
    font-size: 3rem;
    margin-bottom: 10px;
}

.hero p {
    font-size: 1.2rem;
    opacity: 0.9;
}

/* ===== BUTTONS ===== */
.btn {
    display: inline-block;
    margin: 15px;
    padding: 14px 28px;
    background: white;
    color: #4e54c8;
    font-weight: bold;
    border-radius: 30px;
    text-decoration: none;
    transition: 0.3s;
}

.btn:hover {
    transform: translateY(-3px);
    background: #f1f1f1;
}

/* ===== SECTIONS ===== */
.section {
    padding: 60px 10%;
}

.features {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
    gap: 30px;
}

.card {
    background: white;
    border-radius: 18px;
    padding: 30px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    text-align: center;
}

.card i {
    font-size: 45px;
    color: #4e54c8;
    margin-bottom: 15px;
}

.card h3 {
    margin-bottom: 10px;
}

/* ===== FOOTER ===== */
.footer {
    background: #1e1e2f;
    color: #ccc;
    text-align: center;
    padding: 20px;
}
</style>
</head>

<body class="page-layout">

<!-- PAGE CONTENT -->
<div class="page-content">

    <!-- HERO -->
    <section class="hero">
        <h1>
            <i class="fa-solid fa-graduation-cap"></i>
            Student Registration System
        </h1>
    </section>

    <!-- QUICK ACCESS -->
    <section class="section" style="background:#f4f6f9;">

        <div class="features">
            <div class="card">
                <i class="fa fa-user-cog"></i>
                <h3>Admin Panel</h3>
                <a href="login.php" class="btn">Admin Login</a>
            </div>

            <div class="card">
                <i class="fa fa-id-card"></i>
                <h3>Student Portal</h3>
                <a href="login.php" class="btn">Student Login</a>
            </div>
        </div>
    </section>

</div>
<!-- END PAGE CONTENT -->

<!-- FOOTER -->
<div class="footer">
    <p>
        <i class="fa fa-copyright"></i>
        <?= date('Y') ?> Student Registration System |
        NTA5 DB-KIITEC
    </p>
</div>

</body>
</html>
