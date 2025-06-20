
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
    input[type="password"] {
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

    <label for="new_password">New Password</label>
    <input type="password" id="new_password" name="new_password" required />

    <label for="confirm_password">Confirm Password</label>
    <input type="password" id="confirm_password" name="confirm_password" required />

    <button type="submit">Reset Password</button>
    <div class="message" id="msg"></div>
  </form>

  <script>
    function getTokenFromURL() {
      const params = new URLSearchParams(window.location.search);
      return params.get('token');
    }

    const form = document.getElementById('resetForm');
    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      const token = getTokenFromURL();
      const new_password = document.getElementById('new_password').value;
      const confirm_password = document.getElementById('confirm_password').value;
      const msg = document.getElementById('msg');

      if (!token) {
        msg.textContent = "❌ Reset token is missing from the URL.";
        msg.style.color = "red";
        return;
      }

      if (new_password !== confirm_password) {
        msg.textContent = "❌ Passwords do not match.";
        msg.style.color = "red";
        return;
      }

      try {
        const response = await fetch('https://zerowaste-cgdtdqhpcuhxceb2.uaenorth-01.azurewebsites.net/reset_password.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ token, new_password, confirm_password })
        });

        const result = await response.json();
        msg.textContent = result.message || result.error;
        msg.style.color = response.ok ? "green" : "red";
      } catch (err) {
  const msg = document.getElementById('msg');

  if (err instanceof TypeError) {
    // Network-level error (e.g. server down, CORS issues)
    msg.textContent = "❌ Network connection failed.";
  } else {
    // Attempt to parse the error message from the server's response
    try {
      const errorJson = await err.response.json();
      msg.textContent = `❌ ${errorJson.error || "Unexpected error occurred"}`;
    } catch {
      msg.textContent = "❌ Unexpected error. Please try again.";
    }
  }

  msg.style.color = "red";
}
    });
  </script>

</body>
</html>