<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f6f6f6;
      padding: 40px;
    }
    form {
      background: white;
      padding: 20px;
      max-width: 400px;
      margin: auto;
      border-radius: 8px;
      box-shadow: 0 0 10px #ccc;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button {
      background: #28a745;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .message {
      margin-top: 15px;
    }
  </style>
</head>
<body>

  <form id="resetForm">
    <h2>Reset Your Password</h2>
    <label for="token">Reset Token</label>
    <input type="text" id="token" name="token" placeholder="Enter your reset token" required />

    <label for="new_password">New Password</label>
    <input type="password" id="new_password" name="new_password" required />

    <label for="confirm_password">Confirm Password</label>
    <input type="password" id="confirm_password" name="confirm_password" required />

    <button type="submit">Reset Password</button>
    <div class="message" id="msg"></div>
  </form>

  <script>
    const form = document.getElementById('resetForm');
    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      const token = document.getElementById('token').value.trim();
      const new_password = document.getElementById('new_password').value;
      const confirm_password = document.getElementById('confirm_password').value;
      const msg = document.getElementById('msg');

      if (new_password !== confirm_password) {
        msg.innerText = "❌ Passwords do not match!";
        msg.style.color = 'red';
        return;
      }

      const response = await fetch('https://yourserver.com/reset_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, new_password, confirm_password })
      });

      const result = await response.json();
      msg.innerText = result.message || result.error;
      msg.style.color = response.ok ? 'green' : 'red';
    });
  </script>

</body>
</html>