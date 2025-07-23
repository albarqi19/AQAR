<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مميزات النظام - عقارمي</title>
    
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
            line-height: 1.6;
            position: relative;
            overflow-x: hidden;
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
        
        /* الهيدر */
        .header {
            padding: 30px 20px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .back-btn {
            position: absolute;
            top: 30px;
            right: 30px;
            color: #ffffff;
            text-decoration: none;
            font-size: 24px;
            background: rgba(255, 255, 255, 0.1);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }
        
        .logo {
            width: 200px;
            height: auto;
            margin-bottom: 20px;
            filter: brightness(1.1);
        }
        
        .page-title {
            font-size: 3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #ffffff 0%, #7fb069 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-subtitle {
            font-size: 18px;
            color: #cccccc;
            margin-bottom: 50px;
            opacity: 0.8;
        }
        
        /* المحتوى الرئيسي */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        /* شبكة المميزات */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .feature-card::before {
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
        
        .feature-card:hover::before {
            opacity: 1;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            border-color: rgba(127, 176, 105, 0.3);
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(127, 176, 105, 0.1);
        }
        
        .feature-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
            opacity: 0.9;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1);
            opacity: 1;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #ffffff;
            position: relative;
            z-index: 1;
        }
        
        .feature-desc {
            color: #cccccc;
            font-size: 1rem;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }
        
        /* زر العودة للرئيسية */
        .cta-section {
            text-align: center;
            padding: 60px 0;
        }
        
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px 40px;
            color: #ffffff;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .cta-btn:hover {
            transform: translateY(-3px);
            border-color: rgba(127, 176, 105, 0.3);
            background: rgba(127, 176, 105, 0.1);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        /* التجاوب */
        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .feature-card {
                padding: 30px 20px;
            }
            
            .page-title {
                font-size: 2.2rem;
            }
            
            .back-btn {
                top: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .feature-icon {
                font-size: 3rem;
            }
        }
        
        /* تأثيرات الحركة */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .feature-card {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
        .feature-card:nth-child(4) { animation-delay: 0.4s; }
        .feature-card:nth-child(5) { animation-delay: 0.5s; }
        .feature-card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <!-- الهيدر -->
    <div class="header">
        <a href="{{ route('welcome') }}" class="back-btn">←</a>
        <img src="{{ asset('logo.png') }}" alt="عقارمي" class="logo">
        <h1 class="page-title">مميزات النظام</h1>
        <p class="page-subtitle">اكتشف الإمكانيات المتقدمة لنظام إدارة العقارات</p>
    </div>
    
    <div class="container">
        <!-- شبكة المميزات -->
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🏢</span>
                <h3 class="feature-title">إدارة العقارات</h3>
                <p class="feature-desc">إدارة شاملة للعقارات والمباني والمحلات التجارية مع تتبع دقيق لجميع التفاصيل والمعلومات الهامة</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">👥</span>
                <h3 class="feature-title">إدارة المستأجرين</h3>
                <p class="feature-desc">نظام متكامل لإدارة بيانات المستأجرين والتواصل معهم وتتبع تاريخهم الإيجاري بشكل احترافي</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">📋</span>
                <h3 class="feature-title">إدارة العقود</h3>
                <p class="feature-desc">إنشاء وإدارة عقود الإيجار بسهولة مع تتبع تواريخ التجديد والانتهاء والشروط المختلفة</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">💰</span>
                <h3 class="feature-title">إدارة المدفوعات</h3>
                <p class="feature-desc">تتبع دقيق للمدفوعات والمستحقات مع إمكانية إنشاء فواتير وتذكيرات تلقائية للمستأجرين</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">🔧</span>
                <h3 class="feature-title">إدارة الصيانة</h3>
                <p class="feature-desc">نظام شامل لإدارة طلبات الصيانة وتتبع الأعمال المنجزة مع سجل كامل للتكاليف والمقاولين</p>
            </div>
            
            <div class="feature-card">
                <span class="feature-icon">📊</span>
                <h3 class="feature-title">التقارير والإحصائيات</h3>
                <p class="feature-desc">تقارير مفصلة وإحصائيات دقيقة تساعد في اتخاذ القرارات الاستثمارية الصحيحة وتحليل الأداء</p>
            </div>
        </div>
        
        <!-- زر العودة -->
        <div class="cta-section">
            <a href="{{ route('welcome') }}" class="cta-btn">
                <span>العودة للصفحة الرئيسية</span>
                <span>🏠</span>
            </a>
        </div>
    </div>
</body>
</html>
