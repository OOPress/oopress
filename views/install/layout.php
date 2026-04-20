<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'OOPress Installation') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .install-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .install-header {
            background: #1a1e2b;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .install-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .install-header h1 span:first-child {
            color: #FF8C00;
        }
        
        .install-steps {
            display: flex;
            background: #f7fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 30px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            background: #cbd5e0;
            color: white;
            border-radius: 50%;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .step.active .step-number {
            background: #4299e1;
        }
        
        .step.completed .step-number {
            background: #48bb78;
        }
        
        .step-label {
            font-size: 12px;
            color: #718096;
        }
        
        .step.active .step-label {
            color: #2d3748;
            font-weight: 600;
        }
        
        .install-body {
            padding: 30px;
        }
        
        .install-footer {
            background: #f7fafc;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3748;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #4299e1;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3182ce;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .requirements-list {
            margin: 20px 0;
        }
        
        .requirement {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .requirement.passed {
            color: #48bb78;
        }
        
        .requirement.failed {
            color: #e53e3e;
        }
        
        .requirement-name {
            font-weight: 600;
        }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .progress-fill {
            height: 100%;
            background: #4299e1;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1><span>OO</span><span>Press</span> Installation</h1>
            <p>Welcome to OOPress CMS</p>
        </div>
        
        <div class="install-steps">
            <?php $steps = ['Welcome', 'Database', 'Admin', 'Site', 'Install']; ?>
            <?php foreach ($steps as $i => $stepName): ?>
                <div class="step <?= $step === $i + 1 ? 'active' : '' ?> <?= $step > $i + 1 ? 'completed' : '' ?>">
                    <div class="step-number"><?= $i + 1 ?></div>
                    <div class="step-label"><?= $stepName ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="install-body">
            <?= $this->section('content') ?>
        </div>
        
        <div class="install-footer">
            <p>&copy; <?= date('Y') ?> OOPress - Modern PHP CMS</p>
        </div>
    </div>
</body>
</html>