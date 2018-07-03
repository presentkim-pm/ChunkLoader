# <img src="./assets/icon/index.svg" height="50" width="50"> ChunkLoader  
__A plugin for [PMMP](https://pmmp.io) :: Load the chunk you want all the time!__  
  
[![license](https://img.shields.io/github/license/PresentKim/ChunkLoader-PMMP.svg?label=License)](LICENSE)
[![release](https://img.shields.io/github/release/PresentKim/ChunkLoader-PMMP.svg?label=Release)](https://github.com/PresentKim/ChunkLoader-PMMP/releases/latest)
[![download](https://img.shields.io/github/downloads/PresentKim/ChunkLoader-PMMP/total.svg?label=Download)](https://github.com/PresentKim/ChunkLoader-PMMP/releases/latest)
  
## What is this?   
Keeps the registered chunk always loaded
  
  
## Features  
- [x] Register chunk to always loaded  
- [x] Save plugin data in NBT format  
- [x] Support configurable things  
- [x] Check that the plugin is not latest version  
  - [x] If not latest version, show latest release download url  
  
  
## Configurable things  
- [x] Configure the language for messages  
  - [x] in `{SELECTED LANG}/lang.ini` file  
  - [x] Select language in `config.yml` file  
- [x] Configure the command (include subcommands)  
  - [x] in `config.yml` file  
- [x] Configure the permission of command  
  - [x] in `config.yml` file  
  
The configuration files is created when the plugin is enabled.  
The configuration files is loaded  when the plugin is enabled.  
  
  
## Command  
Main command : `/chunkloader <Register | Unregister | List>`  
  
| subcommand | arguments                           | description                        |  
| :--------- | :---------------------------------- | :--------------------------------- |  
| Register   | \[chunkX\] \[chunkZ\] \[WorldName\] | Register chunk to chunk loader     |  
| Unregister | \[chunkX\] \[chunkZ\] \[WorldName\] | Unregister chunk from chunk loader |  
| List       | \[page\]                            | See registered chunk list          |  
  
  
## Permission  
| permission                 | default  | description           |  
| :------------------------- | :------: | :-------------------- |  
| chunkloader.cmd            | OP       | main command          |  
|                            |          |                       |  
| chunkloader.cmd.register   | OP       | register subcommand   |  
| chunkloader.cmd.unregister | OP       | unregister subcommand |  
| chunkloader.cmd.list       | OP       | list subcommand       |  
