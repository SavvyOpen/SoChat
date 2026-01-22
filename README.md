# Secure Real-Time Web Chat System
**Technologies:** PHP, JavaScript, HTML, CSS, Server-Sent Events (SSE), MySQL

A fully-featured real-time chat system built from scratch in vanilla PHP and JS, designed with security, efficiency, and portability in mind. The system demonstrates advanced features that typical frameworks don’t provide by default.  Ideal for **privacy-focused self-hosted deployments** with minimal setup and hosting requirements.

**Only the client-side code is provided for security purposes. This version of the client-side code represents the minimal setup only.**

## Key Features

1. **Secure Web Login System**
   - Robust session management with PHP sessions, protecting against session hijacking.
   - Password security: zero-knowledge on the server, with client-side enhancement.
   - SQL protection: uses prepared statements / parameterized queries to prevent SQL injection.
   - Per-user authentication with session state maintained across requests.
   - **Mandatory Multi-Factor Authentication (MFA) with TOTP**: all users are required to use TOTP for login, with admin-approved enrollment and compatibility with standard authenticator apps.

2. **Real-Time Chat**
   - Uses Server-Sent Events (SSE) for live updates.
   - Sends full chat history on login, then only incremental/differential updates to reduce data transfer.
   - Supports 1:1 and group chats, with per-user chat history.
   - Contacts & interactions: users can view past contacts, request, and accept new interactions.

3. **Single Active Session Enforcement (Bi-directional)**
   - Ensures only one active session per user at any time.
   - New logins automatically disconnect older sessions.
   - Older sessions can resume by refreshing the browser.
   - SSE notifies users immediately when a session is disconnected.
   - Minimum 3-second reconnect interval to prevent abuse.

4. **Portable & Efficient History**
   - All histories are automatically portable — users see the same chat history on any device.
   - Differential updates ensure bandwidth-efficient communication.

5. **Scalability & Efficiency**
   - SSE PHP workers use 5-second sleep intervals to reduce CPU usage while maintaining real-time updates.
   - Designed to work efficiently without heavy frameworks or WebSocket servers.

## Why This Project Stands Out
- Implements advanced features that typical frameworks don’t provide by default, done completely in vanilla PHP and JS.
- Rare for a self-built system: bi-directional single-session enforcement, portable chat history, incremental updates, 1:1 + group chat integration.
- **Demonstrates modern security best practices**, including mandatory password + TOTP MFA with admin-approved enrollment.
- Handles real-world challenges: security, session management, real-time communication, and scalable data updates.

## Demo & Code
- **Live Demo:** [https://SavvyOpen.com/chat](https://SavvyOpen.com/chat)  
- **GitHub Repository:** [https://github.com/SavvyOpen/SoChatX](https://github.com/SavvyOpen/SoChatX)
