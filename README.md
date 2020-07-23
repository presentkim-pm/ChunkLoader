# <img src="https://rawgit.com/PresentKim/SVG-files/master/plugin-icons/chunkloader.svg" height="50" width="50"> ChunkLoader  
__A plugin for [PMMP](https://pmmp.io) :: Load the chunk you want all the time!__  

[![license](https://img.shields.io/github/license/Blugin/ChunkLoader-PMMP.svg?label=License)](./LICENSE)
[![release](https://img.shields.io/github/release/Blugin/ChunkLoader-PMMP.svg?label=Release)](../../releases/latest)
[![download](https://img.shields.io/github/downloads/Blugin/ChunkLoader-PMMP/total.svg?label=Download)](../../releases/latest)
  
## What is this?   
Keeps the registered chunk always loaded
  
  
## Features  
- [x] Register chunk to always loaded  
- [x] Save plugin data in NBT format  
- [x] Support configurable things  
  
  
### Configurable things  
- [x] Configure the language for messages  
  - [x] in `lang/{SELECTED LANG}.ini` file  
  - [x] Select language in `config.yml` file  
- [x] Configure the command (include subcommands)  
  - [x] in `config.yml` file  
- [x] Configure the permission of command  
  - [x] in `config.yml` file  
  
The configuration files is created when the plugin is enabled.  
The configuration files is loaded  when the plugin is enabled.  
  
  
## Command  
Main command : `/chunkloader <Register | Unregister | Clear | List>`  
  
| subcommand | arguments                           | description                        |  
| :--------- | :---------------------------------- | :--------------------------------- |  
| Register   | \[chunkX\] \[chunkZ\] \[WorldName\] | Register chunk to chunk loader     |  
| Unregister | \[chunkX\] \[chunkZ\] \[WorldName\] | Unregister chunk from chunk loader |  
| Clear      | \[WorldName\]                       | Clear all chunk loader of world    |  
| List       | \[WorldName\] \[page\]              | See chunk loader list of world     |  
  
  
## Permission  
| permission                 | default  | description           |  
| :------------------------- | :------: | :-------------------- |  
| chunkloader.cmd            | OP       | main command          |  
|                            |          |                       |  
| chunkloader.cmd.register   | OP       | register subcommand   |  
| chunkloader.cmd.unregister | OP       | unregister subcommand |  
| chunkloader.cmd.clear      | OP       | clear subcommand      |  
| chunkloader.cmd.list       | OP       | list subcommand       |  
