# Study Assistant - AI-Powered Learning Platform

A comprehensive AI-powered study assistant application built with React, TypeScript, and Supabase.

##  Features

###  AI Chat
- Intelligent conversational AI
- Persistent chat history
- Session management

###  Notes Management
- CRUD operations
- Tag-based organization
- Full-text search capability

###  Study Materials
- Organize learning resources
- Categorization system
- URL linking

###  Analytics Dashboard
- Real-time statistics
- Progress tracking
- AI-powered insights

###  Settings
- User preferences
- Profile management
- Notification settings

##  Tech Stack

- React 18 + TypeScript
- Vite
- Tailwind CSS + shadcn/ui
- Supabase (Database + Auth)
- React Router
- TanStack React Query

##  Getting Started

1. Install dependencies: `npm install`
2. Set up `.env` with Supabase credentials
3. Run migrations (see DEPLOYMENT.md)
4. Start dev server: `npm run dev`

##  Database Schema

- profiles (user data + preferences)
- chat_sessions
- chat_messages
- notes
- study_materials

All tables have RLS enabled for security.

##  Security

- Row Level Security on all tables
- User-scoped data isolation
- Secure authentication

##  Documentation

See DEPLOYMENT.md for setup instructions.

---

 2025 Study Assistant
