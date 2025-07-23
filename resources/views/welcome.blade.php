<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport"         /* الأزرار */
        .buttons-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
        }="width=device-width, initial-scale=1">
    <title>عقارمي - نظام إدارة العقارات</title>
    
    <!-- Google Fonts - Cairo for Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Cairo', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #1a1a1a;
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            line-height: 1.6;
            position: relative;
        }
        
        /* تأثير الخلفية */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 25% 25%, rgba(127, 176, 105, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(127, 176, 105, 0.02) 0%, transparent 50%);
            pointer-events: none;
        }
        
        /* الحاوية الرئيسية */
        .container {
            max-width: 600px;
            width: 100%;
            padding: 40px 20px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        /* قسم الشعار */
        .logo-section {
            margin-bottom: 60px;
        }
        
        .logo {
            width: 280px;
            height: auto;
            margin-bottom: 24px;
            filter: brightness(1.1);
            transition: all 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.02);
            filter: brightness(1.2);
        }
        
        .logo-text {
            font-size: 16px;
            color: #888888;
            font-weight: 300;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        /* العنوان الرئيسي */
        .main-title {
            font-size: 3.2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #7fb069 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            font-size: 18px;
            color: #cccccc;
            font-weight: 400;
            margin-bottom: 50px;
            opacity: 0.8;
        }
        
        /* قسم الأزرار */
        .buttons-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
        }
        
        .btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            text-decoration: none;
            color: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(127, 176, 105, 0.05) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .btn:hover::before {
            opacity: 1;
        }
        
        .btn:hover {
            transform: translateY(-8px);
            border-color: rgba(127, 176, 105, 0.3);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(127, 176, 105, 0.1);
        }
        
        .btn-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.9;
            transition: all 0.3s ease;
        }
        
        .btn:hover .btn-icon {
            transform: scale(1.1);
            opacity: 1;
        }
        
        .btn-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #ffffff;
        }
        
        .btn-desc {
            font-size: 14px;
            color: #999999;
            font-weight: 400;
            text-align: center;
            line-height: 1.4;
        }
        
        /* الألوان المخصصة */
        .btn.admin:hover {
            border-color: rgba(59, 130, 246, 0.4);
        }
        
        .btn.admin:hover::before {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, transparent 100%);
        }
        
        .btn.dashboard:hover {
            border-color: rgba(16, 185, 129, 0.4);
        }
        
        .btn.dashboard:hover::before {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, transparent 100%);
        }
        
        .btn.features:hover {
            border-color: rgba(147, 51, 234, 0.4);
        }
        
        .btn.features:hover::before {
            background: linear-gradient(135deg, rgba(147, 51, 234, 0.08) 0%, transparent 100%);
        }
        
        /* خط فاصل */
        .divider {
            width: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            margin: 40px auto;
        }
        
        /* للشاشات الكبيرة - جعل المربعات أكثر تناسقاً */
        @media (min-width: 769px) {
            .btn {
                aspect-ratio: 1;
                min-height: 200px;
                padding: 30px 20px;
            }
            
            .buttons-grid {
                gap: 70px;
                max-width: 1000px;
                margin: 0 auto;
                margin-top: 40px;
            }
        }
        
        /* للشاشات المتوسطة */
        @media (max-width: 768px) {
            .container {
                padding: 30px 16px;
            }
            
            .logo {
                width: 220px;
            }
            
            .main-title {
                font-size: 2.4rem;
            }
            
            .subtitle {
                font-size: 16px;
            }
            
            .buttons-grid {
                grid-template-columns: 1fr;
                gap: 16px;
                max-width: 350px;
                margin: 0 auto;
                margin-top: 40px;
            }
            
            .btn {
                padding: 32px 16px;
            }
            
            .btn-icon {
                font-size: 40px;
            }
            
            .btn-title {
                font-size: 16px;
            }
            
            .btn-desc {
                font-size: 13px;
            }
        }
        
        /* للشاشات الصغيرة */
        @media (max-width: 480px) {
            .container {
                padding: 20px 12px;
            }
            
            .logo {
                width: 180px;
            }
            
            .logo-text {
                font-size: 14px;
                letter-spacing: 2px;
            }
            
            .main-title {
                font-size: 2rem;
            }
            
            .subtitle {
                font-size: 15px;
            }
            
            .buttons-grid {
                grid-template-columns: 1fr;
                gap: 16px;
                max-width: 300px;
                margin: 0 auto;
                margin-top: 40px;
            }
            
            .btn {
                padding: 28px 20px;
            }
            
            .btn-icon {
                font-size: 36px;
            }
            
            .btn-title {
                font-size: 15px;
            }
            
            .btn-desc {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- قسم الشعار -->
        <div class="logo-section">
            <img src="{{ asset('logo.png') }}" alt="عقارمي" class="logo">
            <div class="logo-text">Property Management System</div>
        </div>
        
        <!-- العنوان الرئيسي -->
        <p class="subtitle">نظام إدارة العقارات المتكامل</p>
        
        <div class="divider"></div>
        
        <!-- الأزرار -->
        <div class="buttons-grid">
            <a href="http://127.0.0.1:8001/admin" class="btn admin">
                <div class="btn-icon">⚙️</div>
                <div class="btn-title">لوحة الإدارة</div>
                <div class="btn-desc">إدارة العقارات والمستأجرين والعقود</div>
            </a>
            
            <a href="http://localhost:8080/" class="btn dashboard">
                <div class="btn-icon">📊</div>
                <div class="btn-title">لوحة التقارير</div>
                <div class="btn-desc">عرض الإحصائيات والتقارير المالية</div>
            </a>
            
            <a href="{{ route('features') }}" class="btn features">
                <div class="btn-icon">✨</div>
                <div class="btn-title">مميزات النظام</div>
                <div class="btn-desc">تعرف على المميزات والإمكانيات المتاحة</div>
            </a>
        </div>
    </div>
</body>
</html>
