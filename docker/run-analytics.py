import json
import os
import pwd


def main():
    with open('/run/analytics-environment.json') as f:
        settings = json.load(f)
    if settings.get('ANALYTICS_ENABLED', 'false').lower() != 'true':
        return
    os.environ.update(settings)
    user = pwd.getpwnam('www-data')
    os.initgroups(user.pw_name, user.pw_gid)
    os.setgid(user.pw_gid)
    os.setuid(user.pw_uid)
    os.execv('/opt/analytics/bin/python',
             ['/opt/analytics/bin/python', '/opt/analytics/update_view_count.py'])


if __name__ == '__main__':
    main()
