import yaml
import sys

def update_yaml(file_path, new_data):
    with open(file_path, 'r', encoding='utf-8') as f:
        data = yaml.safe_load(f) or {}
    
    if 'admin_security' not in data:
        data['admin_security'] = {}
    
    data['admin_security'].update(new_data)
    
    with open(file_path, 'w', encoding='utf-8') as f:
        yaml.dump(data, f, allow_unicode=True, sort_keys=False)

en_data = {
    'page_title': 'Security Configuration',
    'title': 'Security',
    'tabs': {
        'email_password': 'Email & Password',
        '2fa_settings': '2FA Settings'
    },
    'alert': {
        'title': 'Add a strong backup method to add more protection',
        'content': 'Having one strong method is good, but having two is even better for protecting your account. If you lose access to your main method, you can use the other one as a backup.'
    },
    'list': {
        'title': 'Your password reset method (2FA)',
        'subtitle': 'Add a layer of protection to your account'
    },
    'btn': {
        'initialize': 'Initialize Defaults',
        'add_method': 'Add new method',
        'cancel': 'Cancel',
        'save': 'Save Changes'
    },
    'status': {
        'more_secure': 'MORE SECURE',
        'less_secure': 'LESS SECURE'
    },
    'modal': {
        'title': 'Configure %name%',
        'priority_label': 'Priority Order',
        'priority_help': 'Lower numbers are checked first (e.g. 10 runs before 20).',
        'enable_method': 'Enable this method'
    },
    'side_card': {
        'checkup': {
            'title': 'Security Checkup',
            'text': 'Add more protection to your account through these steps.',
            'link': 'Get started'
        },
        'help': {
            'title': 'Need help?',
            'text': "Let us know if anything isn't working as you expect.",
            'link': 'Get support'
        }
    }
}

zh_data = {
    'page_title': '安全配置管理',
    'title': '安全配置',
    'tabs': {
        'email_password': '邮箱与密码',
        '2fa_settings': '双重认证设置'
    },
    'alert': {
        'title': '添加强大的备用方法以增加更多保护',
        'content': '拥有一个强大的方法很好，但拥有两个方法可以更好地保护您的帐户。如果您失去了对主要方法的访问权限，您可以使用另一个方法作为备用。'
    },
    'list': {
        'title': '您的密码重置方法 (2FA)',
        'subtitle': '为您的帐户添加一层保护'
    },
    'btn': {
        'initialize': '初始化默认配置',
        'add_method': '添加新方法',
        'cancel': '取消',
        'save': '保存更改'
    },
    'status': {
        'more_secure': '更安全',
        'less_secure': '较不安全'
    },
    'modal': {
        'title': '配置 %name%',
        'priority_label': '优先级顺序',
        'priority_help': '数字越小越先检查（例如 10 优先于 20）。',
        'enable_method': '启用此方法'
    },
    'side_card': {
        'checkup': {
            'title': '安全检查',
            'text': '通过这些步骤为您的帐户添加更多保护。',
            'link': '开始使用'
        },
        'help': {
            'title': '需要帮助？',
            'text': '如果遇到任何问题，请告诉我们。',
            'link': '获取支持'
        }
    }
}

update_yaml('translations/messages.en.yaml', en_data)
update_yaml('translations/messages.zh_CN.yaml', zh_data)
print("Translations updated successfully.")
