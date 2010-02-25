--[[

	A player command to set a message of the day

]]


return function(cn,text)

	if not text then

		return false, "#motd \"<text>\""
	end

	server.motd = text
	server.player_msg(cn,"MOTD changed to " .. text)

end
