import os
import glob

# 1. Update style.css body background
style_css_path = 'assets/css/style.css'
with open(style_css_path, 'r', encoding='utf-8') as f:
    style_css = f.read()

# Check if we already added background-image to body
if 'radial-gradient' not in style_css.split('body {')[1].split('}')[0]:
    style_css = style_css.replace(
        '  background-color: var(--bg-primary);',
        '  background-color: var(--bg-primary);\n  background-image: radial-gradient(circle at 15% 50%, var(--accent-light), transparent 25%), radial-gradient(circle at 85% 30%, var(--accent-light), transparent 25%);\n  background-attachment: fixed;\n  background-size: cover;'
    )
    with open(style_css_path, 'w', encoding='utf-8') as f:
        f.write(style_css)
    print("Updated style.css")

# 2. Update logos in all php files
def update_logos(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    modified = False
    
    # Check if we are in a subfolder or root folder to set correct image path
    depth = filepath.count('/') + filepath.count('\\')
    # Because all these files are in root (0 depth) or 1 depth (includes/header.php, auth/login.php)
    img_prefix = '../' if depth > 0 and 'FINAL_TEST\\' not in filepath else ''
    if 'FINAL_TEST\\' in filepath:
        # relative to FINAL_TEST root
        rel_path = filepath.split('FINAL_TEST\\')[1]
        depth = rel_path.count('\\')
        img_prefix = '../' * depth
    
    logo_path = f"{img_prefix}assets/images/logo.png"
    circle_logo_html = f'<img src="{logo_path}" alt="EventHub Logo" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid var(--accent);">'
    
    # Replace emoji logo
    if '<span style="color: var(--accent);">🎯</span>' in content:
        content = content.replace('<span style="color: var(--accent);">🎯</span>', circle_logo_html)
        modified = True
        
    # Replace existing image logo without circle class
    old_img_logo = f'<img src="{img_prefix}assets/images/logo.png" alt="EventHub Logo" style="height: 32px; width: auto;">'
    if old_img_logo in content:
        content = content.replace(old_img_logo, circle_logo_html)
        modified = True

    # Also replace footer brand emoji if present
    if '🎯 EventHub' in content and 'footer-brand' in content:
        content = content.replace('🎯 EventHub', f'{circle_logo_html} EventHub')
        modified = True
        
    if modified:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated logos in {filepath}")

php_files = glob.glob('**/*.php', recursive=True)
for f in php_files:
    update_logos(f)
