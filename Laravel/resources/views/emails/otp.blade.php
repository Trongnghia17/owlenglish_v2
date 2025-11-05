<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Mã OTP</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f7f9fc;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      margin: 40px auto;
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      padding: 30px;
      text-align: center;
      border-top: 6px solid rgb(5, 55, 165);
    }
    .logo {
      margin-bottom: 20px;
    }
    .otp-box {
      background: rgba(5, 55, 165, 0.08);
      border: 1px dashed rgb(5, 55, 165);
      border-radius: 6px;
      font-size: 22px;
      font-weight: bold;
      color: rgb(5, 55, 165);
      padding: 15px 20px;
      display: inline-block;
      margin: 20px 0;
      letter-spacing: 3px;
    }
    .footer {
      margin-top: 30px;
      font-size: 13px;
      color: #6b7280;
    }
    .highlight {
      color: rgb(5, 55, 165);
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo">
      <img src="https://owlenglish.vn/wp-content/uploads/2025/07/Artboard-1-1-1226x800.png" alt="Owl English" width="180">
    </div>
    <p>Xin chào,</p>
    <p>Mã OTP của bạn là:</p>
    <div class="otp-box">{{ $otp }}</div>
    <p>Mã này chỉ có hiệu lực trong <span class="highlight">5 phút</span>.<br>
    Vui lòng không chia sẻ mã này cho bất kỳ ai.</p>
    <div class="footer">
      © 2025 Owl English. Mọi quyền được bảo lưu.
    </div>
  </div>
</body>
</html>
