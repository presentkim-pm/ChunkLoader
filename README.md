# <img src="https://rawgit.com/PresentKim/SVG-files/master/plugin-icons/chunkloader.svg" height="50" width="50"> ChunkLoader  
__A plugin for [PMMP](https://pmmp.io) :: Load the chunk you want all the time!__  

[![license](https://img.shields.io/github/license/Blugin/ChunkLoader-PMMP.svg?label=License)](./LICENSE)
[![release](https://img.shields.io/github/release/Blugin/ChunkLoader-PMMP.svg?label=Release)](../../releases/latest)
[![download](https://img.shields.io/github/downloads/Blugin/ChunkLoader-PMMP/total.svg?label=Download)](../../releases/latest)
  
## What is this?   
This plugin supports the feature to set a specific chunk to always be loaded by the user  
The chunk of your choice always remains loaded  
  
  
## Features  
- [x] The chunk of your choice always remains loaded  
- [x] Chunk list saved as `chunkloads.json` in each world folder  
- [x] Support configurable things  
  - Config file : `config.yml`
    - [x] Select language
    - [x] Configure the command (name/aliases/permissions)
    - [x] Configure the subcommands (name/aliases/permissions)
  - Language file : `lang/{SELECTED LANG}.ini`
    - [x] Configure the messages  
      - in `lang/{SELECTED LANG}.ini` file  
    - [x] Configure the command (include subcommands)  
      - in `config.yml` file  
    - [x] Configure the permission of command  
      - in `config.yml` file  
  
## Required dependency plugins
- [**Blugin/libPluginLang-PMMP**](https://github.com/Blugin/libPluginLang-PMMP) 
 - Download : 
- [**Blugin/libSubcommands-PMMP**](https://github.com/Blugin/libSubcommands-PMMP)


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
