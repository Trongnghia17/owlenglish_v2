<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Truy cập bị từ chối | Hệ thống Giáo dục</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Noto+Sans+Vietnamese:wght@500;700&display=swap');
        
        :root {
            --primary: #2563eb;
            --error: #dc2626;
            --light: #f8fafc;
            --dark: #1e293b;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Noto Sans Vietnamese', 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }
        
        .container {
            text-align: center;
            padding: 2rem;
            width: 90%;
            max-width: 480px;
            position: relative;
            z-index: 10;
        }
        
        .error-graphic {
            width: 160px;
            height: 160px;
            margin: 0 auto 1.5rem;
            position: relative;
        }
        
        .shield {
            position: absolute;
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 5rem;
            color: var(--error);
            border: 8px solid #fee2e2;
            animation: gentlePulse 4s ease-in-out infinite;
        }
        
        .error-code {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--error);
            margin: 1rem 0 0.5rem;
        }
        
        .message {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0.5rem 0 1.5rem;
            line-height: 1.4;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 2rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 999px;
            font-weight: 600;
            margin-top: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            background: #1d4ed8;
        }
        
        .btn-back:active {
            transform: translateY(0);
        }
        
        .btn-back::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-back:hover::before {
            left: 100%;
        }
        
        /* Icon mũi tên quay lại */
        .btn-back span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back span::before {
            content: '←';
            font-size: 1.1em;
        }
        
        /* Background elements */
        .bg-element {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            z-index: 1;
        }
        
        .bg-1 {
            width: 300px;
            height: 300px;
            background: var(--primary);
            top: -100px;
            right: -100px;
            animation: float 15s ease-in-out infinite;
        }
        
        .bg-2 {
            width: 200px;
            height: 200px;
            background: var(--error);
            bottom: -50px;
            left: -50px;
            animation: float 12s ease-in-out infinite reverse;
        }
        
        /* Animations */
        @keyframes gentlePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        /* Micro-interaction for shield */
        .shield::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid var(--error);
            border-radius: 50%;
            animation: ripple 3s linear infinite;
            opacity: 0;
        }
        
        @keyframes ripple {
            0% { transform: scale(0.8); opacity: 0.3; }
            100% { transform: scale(1.5); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="bg-element bg-1"></div>
    <div class="bg-element bg-2"></div>
    
    <div class="container">
        <div class="error-graphic">
            <div class="shield">!</div>
        </div>
        
        <h1 class="error-code">Lỗi 403</h1>
        <p class="message">Bạn không có quyền truy cập trang này</p>
        
        <a href="javascript:history.back()" class="btn-back">
            <span>Quay lại</span>
        </a>
    </div>
</body>
</html>