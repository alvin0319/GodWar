# GodWar
A Minigame plugin for PocketMine-MP: GodWar

# Step by setup
`config.yml`
```yaml
# default is null, you need to replace here.
# ex) world-zip: world.zip
world-zip: ~

# It is time for the game to progress, default: 2000 (seconds)
time: 2000


# The number of rooms to be created.
room: 2

# The number of min player count.
min-count: 4
# The number of max player count, MUST larger than min count.
max-count: 8

red-spawn: "0:0:0:world"
blue-spawn: "0:0:0:world"
```
And you need to put the world zipped file in path/to/plugin_data/GodWar/your_zip_name.zip.

# Jobs

> Zeus:
>> Skill 1: Block all players' skill use.
>>
>> Skill 2: Can fly for 10 seconds.
>
> Ares:
>> Skill 1: Level 1 power buff on team.
>>
>> Skill 2: Fire a trident. The opponent who hit the trident takes 5 damage and burns for 5 seconds.
>>
> Helios:
>> Skill 1: Blocks the opposing player's vision within 8 spaces around you.
>>
>> Skill 2: Ignite opponents within 10 squares.
>
> Gaia:
>> Skill 1: Bind opponents within 6 squares of me to the floor.
>>
>> Skill 2: Gain 2 protection for 5 seconds.
>
> Poseidon:
>> Skill 1: Dash Forward.
>>
>> Skill 2:
>Fire a trident. The opponent who hit the trident will burn and take 10 damage.
>
> Hypnos:
>> Skill 1: Puts all players to sleep.
>>
>> Skill 2: Recovers your health by 8.

# Rule
If you break the opponent's core block (diamond), you win. However, you must break it with your hands.