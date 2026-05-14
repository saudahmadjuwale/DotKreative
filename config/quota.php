<?php
return [
  'total_bytes'    => (int) env('QUOTA_TOTAL_GB', 15) * 1024 * 1024 * 1024,
  'warn_percent'   => (int) env('QUOTA_WARN_PERCENT', 90),
  'max_file_bytes' => (int) env('QUOTA_MAX_FILE_MB', 300) * 1024 * 1024,
];
