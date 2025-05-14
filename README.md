# Echo Realms Documentation

- **Date**: May 14, 2025
- **Project**: Echo Realms - A strategic, turn-based Laravel game.
- **Tech Stack**: Laravel 12, Livewire 3.x, Tailwind CSS 3.x,
Alpine.js 3.x, Vite, Laravel Breeze, PHP 8.1+, MySQL.
- **Authors**: Developed by Roshdi Raed

## Table of Contents
1. [Overview](#overview)
2. [Game Architecture](#game-architecture)
3. [Reported Issues](#reported-issues)
4. [Fixes Applied](#fixes-applied)
5. [Project Setup](#project-setup)
6. [Codebase Details](#codebase-details)
7. [Professional Enhancements](#professional-enhancements)
8. [Debugging and Testing](#debugging-and-testing)
9. [Troubleshooting](#troubleshooting)
10. [Conclusion](#conclusion)

## Overview

Echo Realms is a strategic, turn-based, two-player web game built with Laravel 12. Players manage five "Echo Slots," each containing an action (Attack, Shield, Swap, Reveal, Mimic), and take turns selecting two slots and a target to outmaneuver their opponent. The goal is to reduce the opponent’s health to zero. The game features a sci-fi aesthetic with an animated background, sound effects, user profiles, a points system, and real-time updates.

### Key Features
- **Gameplay**: Players select two slots and a target, with actions like Attack (deal 2 damage), Shield (block attack), Swap (reposition slots), Reveal (view opponent’s slot), and Mimic (copy last action).
- **UI/UX**: Tailwind CSS 3.x, Orbitron font, card flip animations, action animations, tutorial modal.
- **Interactivity**: Alpine.js for dynamic UI, Livewire for real-time updates, Howler.js for sound effects, Particles.js for animated background.
- **User System**: Laravel Breeze for authentication, user profiles with avatars, points, and leaderboards.
- **Stats and Points**: Tracks actions (attacks, shields, etc.) and awards points (100 for wins, 20 for losses, plus action-based bonuses).

## Game Architecture

### Database Schema
- **users**: Stores user data (id, name, email, password) via Laravel Breeze.
- **games**: Tracks game state (id, status [waiting/active/finished], current_player_id, winner_id).
- **players**: Links users to games (id, game_id, user_id, health).
- **echo_slots**: Stores slot actions (id, game_id, player_id, action, position).
- **profiles**: Stores user stats (user_id, points, games_played, wins, avatar).
- **game_stats**: Tracks per-game stats (game_id, user_id, stats JSON).

### Key Components
- **Controllers**:
  - `GameController.php`: Handles game creation, joining, and display.
- **Livewire Components**:
  - `GameBoard.php`: Manages game logic, slot selection, turn submission, stats, and music toggle.
- **Blade Views**:
  - `welcome.blade.php`: Homepage with leaderboard and profiles.
  - `game-board.blade.php`: Game interface with slots, health bars, buttons, and modal.
- **Frontend**:
  - Tailwind CSS for styling.
  - Alpine.js for dynamic UI (e.g., modal toggle, card flips).
  - Vite for asset compilation.
  - Particles.js for animated background.
  - Howler.js for sound effects and music.

## Reported Issues

1. **Tutorial and Mute Music Buttons Not Appearing**:
   - Location: `game-board.blade.php` (top-right buttons).
   - Suspected Cause: Alpine.js not loaded or JavaScript errors preventing initialization.
2. **Tutorial Modal Not Displaying**:
   - Location: `game-board.blade.php` (modal with `x-show="showTutorial"`).
   - Suspected Cause: Alpine.js failure or `showTutorial` state not toggling.
3. **Submit Turn Button Not Appearing**:
   - Location: `game-board.blade.php` (conditional on `$game->current_player_id == Auth::id()`).
   - Suspected Cause: Game state issue (`current_player_id` mismatch) or Livewire rendering problem.
4. **JavaScript-Related Problems**:
   - Suspected Cause: Errors in `particles.js`, `howler.js`, or Alpine.js initialization, or Vite compilation issues.
5. **Previous Errors**:
   - Nested ternary operator error in `game-board.blade.php` (fixed in this document).
   - `$stats` undefined (fixed in `GameBoard.php`).

## Fixes Applied

### 1. Tutorial and Mute Music Buttons/Modal
**Diagnosis**:
- Buttons use Alpine.js directives (`@click="showTutorial = true"`, `@click="toggleMusic"`, `x-text`).
- Modal uses `x-show="showTutorial"`.
- If buttons or modal don’t appear, Alpine.js is likely not loaded, or JavaScript errors halt execution.

**Fixes**:
- Ensured Alpine.js is included in `resources/js/app.js` and compiled by Vite.
- Added error handling in `game-board.blade.php`’s `<script>` for `howler.js` and `particles.js`.
- Added a **Test Alpine** button (`@click="alert('Alpine.js is working!')"`) to verify Alpine.js functionality.
- Verified CDN scripts (`particles.js`, `howler.js`) load correctly.

### 2. Submit Turn Button
**Diagnosis**:
- Button is conditional: `@if ($game->current_player_id == Auth::id())`.
- Debug output (`Current Player ID: {{ $game->current_player_id }} | Auth ID: {{ Auth::id() }}`) shows if `current_player_id` matches `Auth::id()`.
- Likely cause: `GameController.php` or `GameBoard.php` not setting `current_player_id` correctly.

**Fixes**:
- Updated `GameController.php` to set `current_player_id` to the first player’s `user_id` when a game becomes active.
- Added validation in `GameBoard.php`’s `mount` to ensure the user is a game participant.
- Kept debug output to diagnose `current_player_id` mismatches.

### 3. Nested Ternary Operators
**Diagnosis**:
- Nested ternaries in `game-board.blade.php`’s Echo Slots section caused a PHP 8.1+ error (`Unparenthesized 'a ? b : c ? d : e'`).
- Example: `bg-{{ ((($lastAction == 'attack' ? 'red' : $lastAction == 'shield') ? 'blue' : ... }}-500`.

**Fixes**:
- Replaced nested ternaries with `@php` and `match` expressions for action animations and card colors.
- Added parentheses to all simple ternaries (e.g., `($game->winner_id == Auth::id()) ? 'You' : 'Opponent'`) for clarity.

### 4. JavaScript Issues
**Diagnosis**:
- JavaScript errors could prevent Alpine.js initialization, affecting buttons and modal.
- Possible issues: Missing Alpine.js, Vite compilation failure, CDN errors, or Livewire polling conflicts.

**Fixes**:
- Ensured `resources/js/app.js` includes Alpine.js and is compiled by Vite.
- Added try-catch blocks in `game-board.blade.php`’s `<script>` to log errors for `howler.js` and `particles.js`.
- Verified CDN scripts and added fallback to local Alpine.js if needed.

## Project Setup

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 18.x+
- MySQL
- Git

### Installation
1. **Clone Repository** (or create a new Laravel project):
   ```bash
   composer create-project laravel/laravel echo-realms
   cd echo-realms
   ```
2. **Install Dependencies**:
   ```bash
   composer require laravel/breeze livewire/livewire
   npm install
   npm install tailwindcss@latest postcss@latest autoprefixer@latest alpinejs@latest
   npx tailwindcss init -p
   ```
3. **Setup Breeze**:
   ```bash
   php artisan breeze:install blade
   npm run build
   ```
4. **Configure Environment**:
   ```bash
   cp .env.example .env
   nano .env
   ```
   Set:
   ```env
   APP_NAME="Echo Realms"
   APP_URL=http://localhost:8000
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=skillforge
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```
5. **Generate Key**:
   ```bash
   php artisan key:generate
   ```

### Database Setup
1. **Create Database**:
   ```bash
   mysql -u root -p
   CREATE DATABASE skillforge;
   EXIT;
   ```
2. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

### File Structure
- `app/Http/Controllers/GameController.php`: Game creation, joining, display.
- `app/Livewire/GameBoard.php`: Game logic and UI.
- `app/Models/{Game,Player,EchoSlot,Profile,GameStat}.php`: Eloquent models.
- `resources/views/welcome.blade.php`: Homepage.
- `resources/views/livewire/game-board.blade.php`: Game interface.
- `resources/css/app.css`: Tailwind CSS.
- `resources/js/app.js`: Alpine.js.
- `vite.config.js`: Vite configuration.
- `public/sounds/`: Audio files (attack.mp3, shield.mp3, swap.mp3, reveal.mp3, mimic.mp3, background.mp3).
- `database/migrations/`: Schema for games, players, echo_slots, profiles, game_stats.
