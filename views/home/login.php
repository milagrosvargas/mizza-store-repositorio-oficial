<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<title>MizzaStore - Argentina</title>
<style>
    * {
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        min-height: 100vh;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(to right, #EBD4CB, #B6465F);
        display: flex;
        flex-direction: column;
    }

    main {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .container {
        background: #fff;
        width: 100%;
        max-width: 380px;
        padding: 30px 25px;
        border-radius: 12px;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.15);
        text-align: center;
    }

    .tabs {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .tabs span {
        cursor: pointer;
        margin: 0 15px;
        padding-bottom: 6px;
        font-weight: 600;
        border-bottom: 2px solid transparent;
        color: #999;
        transition: 0.3s ease;
    }

    .tabs span.active {
        color: #000;
        border-color: #3498db;
    }

    .icon-user {
        width: 64px;
        height: 64px;
        margin: 10px auto 20px;
    }

    .icon-user img {
        width: 100%;
        height: auto;
    }

    form {
        display: none;
        flex-direction: column;
    }

    form.active {
        display: flex;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 12px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
    }

    .password-wrapper {
        position: relative;
    }

    .btn {
        background: #4e0313ff;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 6px;
        font-size: 15px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .btn:hover {
        background: #2C0703;
        color: #fff;
    }

    .forgot {
        margin-top: 15px;
        font-size: 14px;
    }

    .forgot a {
        color: #350216ff;
        text-decoration: none;
    }

    .footer {
        text-align: center;
        padding: 20px 10px;
        font-size: 13px;
        color: #666;
        background-color: rgba(255, 255, 255, 0.2);
    }

    .footer span {
        display: block;
        margin-top: 8px;
        font-size: 12px;
        color: #7f8c8d;
        letter-spacing: 0.3px;
    }

    @media (max-width: 420px) {
        .container {
            padding: 25px 20px;
        }

        .tabs span {
            margin: 0 10px;
            font-size: 14px;
        }

        input,
        .btn {
            font-size: 14px;
        }
    }

    .input-wrapper {
        position: relative;
        display: block;
        width: 100%;
    }

    .input-wrapper input {
        width: 100%;
        padding: 12px 40px 12px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        box-sizing: border-box;
    }

    .toggle-pass {
        position: absolute;
        top: 40%;
        right: 12px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #888;
        font-size: 18px;
        transition: color 0.2s;
    }

    .toggle-pass:hover {
        color: #333;
    }
</style>


<main>
    <div class="container">
        <div class="tabs">
            <span id="btn-login" class="active">Iniciar sesión</span>
            <span id="btn-register">Registrarme</span>
        </div>

        <div class="icon-user">
            <img src="assets/images/logo.png" alt="User Icon">
        </div>

        <form id="form-login" class="active" method="POST" action="index.php?controller=login&action=autenticar" method="POST">
            <input type="text" name="login_identificador"" placeholder="Usuario, email o teléfono" required>

            <div class="input-wrapper">
                <input type="password" name="password_usuario" placeholder="Contraseña" id="password-login" required>
                <i class="fa fa-eye toggle-pass" onclick="togglePassword('password-login', this)"></i>
            </div>


            <button type="submit" class="btn">Iniciar sesión</button>

            <div class="forgot">
                <a href="#" id="link-olvide-clave">¿Olvidaste tu contraseña?</a>
            </div>
        </form>

        <form id="form-register" method="POST" action="#" novalidate>
            <input type="text" name="nombre_completo" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="text" name="username" placeholder="Nombre de usuario" required>

            <div class="password-wrapper">
                <input type="password" name="password" placeholder="Contraseña" id="password-register" required>
                <span class="toggle-password" onclick="togglePassword('password-register')">👁️</span>
            </div>

            <button type="submit" class="btn">Registrarme</button>
        </form>
    </div>
</main>
<script>
    function togglePassword(fieldId, el) {
        const input = document.getElementById(fieldId);
        if (input.type === "password") {
            input.type = "text";
            el.classList.remove("fa-eye");
            el.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            el.classList.remove("fa-eye-slash");
            el.classList.add("fa-eye");
        }
    }
</script>
<script src="assets/js/login.js"></script>