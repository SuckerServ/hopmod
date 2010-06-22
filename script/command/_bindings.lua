player_command_script("stats")
player_command_script("players")
player_command_script("cheater")
player_command_script("admin")
player_command_script("master")
player_command_script("votekick")
player_command_script("msg")
player_command_script("invadmin")
player_command_script("whoisonline")
player_command_script("invmaster")
player_command_script("nextmap")
player_command_script("uptime")

player_command_script("givemaster",  nil, "master")
player_command_script("mute",        nil, "master")
player_command_script("unmute",      nil, "master")
player_command_script("mutespecs",   nil, "master")
player_command_script("unmutespecs", nil, "master")
player_command_script("names",       nil, "master")
player_command_script("specall",     nil, "master")
player_command_script("unspecall",   nil, "master")
player_command_script("warning",     nil, "master")
player_command_alias("warn", "warning")
player_command_script("recorddemo",  nil, "master")
player_command_alias("demo", "recorddemo")

player_command_script("pause",       nil, "admin")
player_command_script("resume",      nil, "admin")
player_command_script("maxclients",  nil, "admin")
player_command_script("reload",      nil, "admin")
player_command_script("changetime",  nil, "admin")
player_command_alias("time",     "changetime")
player_command_alias("ctime",    "changetime")
player_command_alias("timeleft", "changetime")
player_command_script("motd",        nil, "admin")
player_command_script("group",       nil, "admin")
player_command_script("ban",         nil, "admin")
player_command_script("persist",     nil, "admin")

player_command_script("unban",       nil, "admin")
player_command_script("permban",     nil, "admin")
player_command_script("eval",        nil, "admin")
player_command_script("nosd",        nil, "admin")
player_command_script("sd",          nil, "admin")
player_command_script("slay",        nil, "admin")
player_command_script("giveadmin",   nil, "admin")
player_command_script("forcespec",   nil, "admin")
player_command_alias("fspec", "forcespec")
player_command_script("unforcespec", nil, "admin")
player_command_alias("unfspec", "unforcespec")
player_command_script("setnextmap", nil, "admin")
player_command_script("specmsg", nil, "admin")
player_command_script("versus", nil, "admin")
player_command_alias("specchat", "specmsg")
player_command_alias("sm",       "specmsg")
player_command_alias("schat",    "specmsg")
player_command_alias("sc",       "specmsg")

