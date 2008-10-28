
exec "scripts/scriptpipe.csl"
exec "scripts/irc.csl"
exec "scripts/teamkills.csl"
exec "scripts/commands.csl"
exec "scripts/maps.csl"

logfile "logs/server.log" server_log
log = [
    reference text arg1
    if $log_timestamp [server_log (format "[%1] %2" (time (now)) $text)] [server_log $text]
]

event_handler $onconnect [
    reference cn arg1
    
    local country_log ""
    if (symbol? country) [country_log = (format "(%1)" (country (player_ip $cn)))]
    
    log (format "%1(%2)(%3)%4 connected" (player_name $cn) $cn (player_ip $cn) $country_log)
    
    if (&& (! (strcmp (player_name $cn) "unnamed")) (symbol? country)) [
        local location (country (player_ip $cn))
        if (strcmp $location "unknown") [location = "unknown location"]
        msg (format "%1 is connected from %2" (grey (player_name $cn)) (grey $location))
    ]
    
    sleep (secs 2) [
        local cn @cn
        local connection_id @(player_conid $cn)
        if (= $connection_id (try player_conid $cn [-1])) [
            privmsg $cn (orange [@title server])
            privmsg $cn $motd
        ]
    ]
    
    if (&& (symbol? showaliases) $enable_showaliases) [
        foreach (privplayers) [
            privmsg $arg1 (info (format "Alt names used by %1: %2" (player_name $cn) (showaliases $cn)))
        ]
    ]
]

event_handler $ondisconnect [
    parameters cn reason
    
    discmsg = "disconnected"
    if (! (= $reason 0)) [discmsg = (format "disconnected (%1)" (disc_reason $reason))]
    
    log (format "%1(%2) %3, connection time: %4" (player_name $cn) $cn $discmsg (duration (player_contime $cn)))
    
    if (= $reason $DISC_KICK) [player_var $cn kicked 1]
    
    if (= $playercount 0) [
        sched [
            if $auto_update server_update
            sched defaultgame
        ]
    ]
]

event_handler $onrename [
    parameters cn oldname newname
    
    log (format "%1(%2) renamed to %3" $oldname $cn $newname)
]

event_handler $onreteam [
    parameters cn oldteam newteam
    
    log (format "%1(%2) changed team to %3" (player_name $cn) $cn $newteam)
]

event_handler $onchmm [
    parameters cn newmode
    
    log (format "mastermode is now %1, was set by %2(%3)" $newmode (player_name $cn) $cn)
]

event_handler $onkick [
    parameters target master mins
    target_id = (format "%1(%2)" (player_name $target) $target)
    if (= $master -1) [master_id = "a server admin"] [master_id = (format "%1(%2)" (player_name $master) $master)]
    if (= $mins -1) [bantime = ""] [bantime = (format "and banned for %1 minutes" $mins)]
    log (format "%1 was kicked by %2 %3" $target_id $master_id $bantime)
]
    
event_handler $ontext [
    parameters cn text
    
    if (! $allow_talk) [
        privmsg $cn (err (concat "Talking is banned at this time because:" $disallow_talk_reason))
        veto 1
    ]
    
    if (player_var $cn mute) [
        privmsg $cn (err "Your voice privilege has been revoked.")
        veto 1
    ]
    
    local cmd 0
    
    if (match "^#.*$" $text) [
        veto 1
        arguments = (split $text " ")
        cmdname = (at (split (at $arguments 0) #) 0)
        filename = (format "./scripts/commands/%1.csl" $cmdname)
        if (path? $filename) [exec $filename] [
            dynamic_command = (concatword playercmd_ $cmdname)
            if (symbol? $dynamic_command) [
                arguments = (erase1st $arguments)
                try do [@dynamic_command @arguments] [
                    log_status [@dynamic_command function failed with error @arg1]
                    privmsg $cn (err "Command failed. Check your arguments and your privilege level.")
                ]
            ] [privmsg $cn (err "Command not found.")]
        ]
        cmd = 1
    ]
    
    if (&& (! $cmd) (strcmp (player_status $cn) "spying")) [
        veto 1
        console (player_name $cn) $text
    ]
    
    log (format "%1(%2): %3" (player_name $cn) $cn $text)
]

event_handler $onsayteam [
    parameters cn text
    log (format "%1(%2)(team): %3" (player_name $cn) $cn $text)
]
    
event_handler $onmapvote [
    parameters cn mode map
    
    local admin (strcmp (player_priv $cn) "admin")
    
    if (|| $admin $mapvoting_enabled) [
        log (format "%1(%2) suggests %3 on map %4" (player_name $cn) $cn $mode $map)
    ] [
        privmsg $cn (err "Map voting is disabled.")
        veto 1
    ]
]

event_handler $onsetmap [
    if $custom_maprotation_enabled setnextmap
    if $custom_gametime_enabled [gametime $custom_gametime]
]

event_handler $onmapchanged [

    if (> $playercount 0) [
        log (format "new game: %1" (gameinfo))
    ]
    
    currentmap = $mapname
    
    if (strcmp $gamemode "coopedit") [
        event_handler $onmapchange [ //cleanup on next map change
            mastermode @mastermode
            cancel_handler
        ]
        if $allow_mm_locked [
            mastermode $MM_LOCKED
            console script [Server is locked for coopedit mode.]
        ] [msg (info "Warning! This server doesn't allow locked mastermode.")]
    ]
    
    if (> $gamecount 1) check_scriptpipe
]
    
event_handler $onnewmap [
    parameters cn size
    
    log (format "%1(%2) set new map of size %3" (player_name $cn) $cn $size)
]

event_handler $onnewmap [
    parameters cn size
    
    log (format "%1(%2) set new map of size %3" (player_name $cn) $cn $size)
]

event_handler $onsetmaster [
    parameters cn set password
    
    if (strcmp (player_priv $cn) "none") [
        log (format "%1(%2) relinquished privileged status." (player_name $cn) $cn)
        currentmaster = -1
    ] [
        log (format "%1(%2) gained %3" (player_name $cn) $cn (player_priv $cn))
        currentmaster = $cn
    ]
]
    
event_handler $onauth [
    parameters cn success authname
    if $success [
        log (format "%1(%2) passed authentication as '%3'." (player_name $cn) $cn $authname)
    ] [log (format "%1(%2) failed authentication as '%3'." (player_name $cn) $cn $authname)]
]

event_handler $onspectator [
    parameters cn spec
    if $spec [action = "joined"] [action = "left"]
    log (format "%1(%2) %3 spectators" (player_name $cn) $cn $action)
]

event_handler $ondeath [
    parameters offender victim
    
    teamkill_update $offender $victim
]

event_handler $onshutdown [
    
    log [Server shutdown @(datetime (now))]
    
    if $irc_enabled [
        log "Terminating the IRC bot"
        log_status "Waiting 2 seconds for the IRC bot to update..."
        server_sleep 2000 //give the ircbot time to read and broadcast the latest log messages
        kill $irc_pid
    ]
    
    if $run_banlist_updater [system "killall updatebanlist"]
]

// Start of auto update
if $auto_update [
    exec scripts/auto-update.csl
    track_file_for_update $SERVER_FILENAME
    track_file_for_update conf/server.conf
    track_file_for_update conf/vars.conf
    track_file_for_update conf/maps.conf
    track_file_for_update scripts/servermod.cs
    track_file_for_update scripts/stats/stats.csl
    track_file_for_update scripts/commands.csl
]
if $record_player_stats [exec scripts/stats/stats.csl]
if $autoteambalance [exec scripts/teambalance.csl]

defaultgame = [
    mastermode $MM_OPEN //must be reset in case it was set to locked or private from a server script
    
    if $default_on_empty [
        changemap $default_mode $default_map
        gametime $default_gametime
    ]
]

if $irc_enabled [
    daemon bin/irc.pl [] logs/irc.log logs/irc.log
]

flood_protection SV_TEXT        (secs 1)
flood_protection SV_SETMASTER   (secs 10)
flood_protection SV_KICK        (secs 30)
flood_protection SV_MAPVOTE     (secs 5)

open_scriptpipe

try load_geoip_data "share/GeoIP.dat" [log_error "Expect 'country' function to fail because the GeoIP database was not loaded."]

printsvinfo = [
    parameters filename
    local output [  Server Title: @title
  Max Players: @maxclients
  Running auto-update: @(yesno $auto_update)
  Running IRC bot: @(yesno $irc_enabled)
  Running stats database: @(yesno $record_player_stats)]
    system [echo "@output" >> @filename]
]

printsvstatus = [
    parameters filename
    local output (format "%1/%2, %3 %4, %5 mins left" $playercount $maxclients $mapname $gamemode $timeleft)
    system [echo "@output" >> @filename]
]

update_banlist = [
    clearbanlist
    exec "conf/banlist.conf"
]
sched update_banlist
if $run_banlist_updater [daemon "bin/updatebanlist" "/dev/null" "/dev/null" "logs/updatebanlist.log"]

log_status "*** Running Hopmod 3.0 for Sauerbraten CTF Edition ***"

if (is_startup) [
    log [Server started @(datetime (now))]
    
    defaultgame
    currentmaster = -1
    allow_talk = 1
    disallow_talk_reason = ""
] [
    log [Reloaded server startup scripts @(datetime (now))]
]
