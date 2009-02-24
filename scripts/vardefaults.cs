
servername "unnamed"
maxclients 6
password ""
motd = "Our server is now running hopmod 3.0"

custom_maprotation_enabled = 1
custom_maprotation_balance = 0

custom_gametime_enabled = 0
custom_gametime = (mins 10)

teamkill_limit_enabled = 1
teamkill_limit = 7    // per game
teamkill_maxrate = 3  // per minute
teamkill_bantime = 30 // minutes

check_unnameds = 0 // will spec all unnameds

check_pings = 0 // will check the pings every 20 secs
check_pings_rate = 25 // in secs
maxping = 300

default_mode = instagib
default_map = complex
default_gametime = (mins 10)
default_on_empty = 0    //change to default game when server becomes empty

mapvoting_enabled = 1

log_timestamp = 1

autoteambalance = 1 //only works for CTF mode

// Stats Configuration
record_player_stats = 1
enable_showaliases = 1
stats_filter_policy_min_frags = 1
stats_filter_policy_min_duration = 60
stats_db_filename = "scripts/stats/data/stats.db"

// Start of IRC configuration
// Run the IRC bot daemon. The IRC bot for hopmod broadcasts the server log to 
// an irc channel and allows you remotely adminstrate your game server.
irc_enabled = 0

irc_adminpw = changemenow
irc_debug = 0
irc_network = irc.us.gamesurge.net
irc_channel = #qs-server
// This is the channel where non standard messages go
irc_monitor_channel = #quicksilver
// It is possible to have multiple command names if you separate them with a pipe. This would allow
// you to have all of you bots answer to one name for common commands like who. 
irc_botcommandname = changeme|changemeall
irc_botname = {changeme}
irc_port = 6667
irc_serverpipe = serverexec
irc_username = changeme
irc_spamlines = 4
irc_commandlog = logs/irccommand.log
irc_serverlogfile = logs/server.log

// Optional Modules - May require additional module installation.

// Player Locator : Locates common clan tags and lists players on other servers.
// Requires additional Modules.
// See instructions on http://hopmod.e-topic.info/index.php5?title=IRC_Bot before use.
irc_player_locator = 0

// End of IRC configuration

// Run the banlist update daemon. The update daemon downloads latest banlist
// file and updates local copy every 5 minutes. The published ban list is 
// maintained by operators in #sauer-bans on irc gamesurge network.
run_banlist_updater = 0

script_socket_server_enabled = 1
script_socket_server_port = 7894

// Server map rotation lists

"ffa/default_maps" = [metl4 deathtek fanatic_quake aard3c metl2 ruby curvedm
 metl3 nmp8 complex douze killfactory lostinspace oasis aqueducts corruption
 thor academy tartech refuge kalking1 orbe wake5 ot fragplaza hog2 roughinery
 shadowed DM_BS1 shindou moonlite darkdeath thetowers kmap5 konkuri-to stemple
 tejen ogrosupply frostbyte fanatic_castle_trap nmp10 island neondevastation
 neonpanic orion katrez_d serpentine ksauer1 pgdm oddworld phosgene sdm1 
 battleofthenile guacamole hades paradigm fanatic_complexities mechanic wdcd]

"instagib_maps" = [complex douze fanatic_quake aard3c metl3 academy hog2 wake5 nmp8]

ctfmaps = [hallo reissen berlin_wall shipwreck akroseum flagstone face-capture
 valhalla urban_c mach2 redemption tejen capture_night l_ctf frostbyte wdcd sctf1]

"ctf_maps" = $ctfmaps
"insta ctf_maps" = $ctfmaps

capturemaps = [river_c paradigm fb_capture urban_c serenity nevil_c lostinspace
 face-capture nmp9 c_valley nmp4 nmp8 fc3 ph-capture monastery corruption hades
 asteroids venice relic frostbyte ogrosupply hallo reissen akroseum duomo
 capture_night c_egypt tejen fc4]

"capture_maps" = $capturemaps
"regen capture_maps" = $capturemaps
"insta capture_maps" = $capturemaps

"small_instagib_maps" = [complex douze academy metl3 aard3c]
"medium_instagib_maps" = [ot orion wake5 id douze academy shadowed nmp8]
"large_instagib_maps" = [fanatic_quake curvedm lostinspace pgdm ksauer1 wdcd
    relic urban_c shadowed]

flood_protection SV_TEXT        (secs 1)
flood_protection SV_SETMASTER   (secs 10)
flood_protection SV_KICK        (secs 30)
flood_protection SV_MAPVOTE     (secs 5)
flood_protection SV_C2SINIT     (secs 1)
