select
    "RefreshMinutes" as refresh_minutes
    , "RefreshEnabled" as refresh_enabled
    , "DebugUser" as debug_user
    , "InfoUser" as info_user
from
    "BackgroundTask"
where
    "Name" = ?
