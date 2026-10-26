import importlib.util
import json
from pathlib import Path
import types
import unittest
from unittest.mock import mock_open, patch

spec = importlib.util.spec_from_file_location('analytics_runner', Path(__file__).with_name('run-analytics.py'))
runner = importlib.util.module_from_spec(spec)
spec.loader.exec_module(runner)


class AnalyticsTests(unittest.TestCase):
    def test_disabled_does_not_start_job(self):
        with patch('builtins.open', mock_open(read_data='{}')), patch.object(runner.os, 'execv') as execute:
            runner.main()
            execute.assert_not_called()

    def test_enabled_drops_privileges_and_runs_updater(self):
        settings = {'ANALYTICS_ENABLED': 'true', 'ANALYTICS_DB_PATH': '/data/database.db'}
        user = types.SimpleNamespace(pw_name='www-data', pw_uid=33, pw_gid=33)
        with patch('builtins.open', mock_open(read_data=json.dumps(settings))), \
             patch.object(runner.pwd, 'getpwnam', return_value=user), \
             patch.object(runner.os, 'initgroups') as groups, \
             patch.object(runner.os, 'setgid') as gid, \
             patch.object(runner.os, 'setuid') as uid, \
             patch.object(runner.os, 'execv') as execute, \
             patch.dict(runner.os.environ, {}, clear=True):
            runner.main()
            groups.assert_called_once_with('www-data', 33)
            gid.assert_called_once_with(33)
            uid.assert_called_once_with(33)
            execute.assert_called_once_with('/opt/analytics/bin/python',
                ['/opt/analytics/bin/python', '/opt/analytics/update_view_count.py'])
            self.assertEqual(runner.os.environ['ANALYTICS_DB_PATH'], '/data/database.db')


if __name__ == '__main__':
    unittest.main()
