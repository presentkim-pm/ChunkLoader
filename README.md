# ChunkLoader [![license](https://img.shields.io/github/license/PresentKim/ChunkLoader-PMMP.svg?label=License)](LICENSE)
<img src="./assets/icon/index.svg" height="256" width="256">  

[![release](https://img.shields.io/github/release/PresentKim/ChunkLoader-PMMP.svg?label=Release)](https://github.com/PresentKim/ChunkLoader-PMMP/releases/latest) [![download](https://img.shields.io/github/downloads/PresentKim/ChunkLoader-PMMP/total.svg?label=Download)](https://github.com/PresentKim/ChunkLoader-PMMP/releases/latest)


A plugin add chunk loader for PocketMine-MP

<br/><br/>

## Command
Main command : `/chunkloader <register | unregister | list>`

| subcommand | arguments                           | description                        |
| :--------- | :---------------------------------- | :--------------------------------- |
| Register   | \[chunkX\] \[chunkZ\] \[WorldName\] | Register chunk to chunk loader     |
| Unregister | \[chunkX\] \[chunkZ\] \[WorldName\] | Unregister chunk from chunk loader |
| List       | \[page\]                            | See registered chunk list          |

<br/><br/>

## Permission
| permission                 | default  | description           |
| :------------------------- | :------: | :-------------------- |
| chunkloader.cmd            | OP       | main command          |
|                            |          |                       |
| chunkloader.cmd.register   | OP       | register subcommand   |
| chunkloader.cmd.unregister | OP       | unregister subcommand |
| chunkloader.cmd.list       | OP       | list subcommand       |

<br/><br/>

## Required API
- PocketMine-MP : higher than [Build #937](https://jenkins.pmmp.io/job/PocketMine-MP/937)
