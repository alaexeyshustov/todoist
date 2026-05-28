<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen flex flex-col bg-gray-50" x-data="todoApp()" x-init="init()">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between shrink-0">
        <h1 class="text-lg font-semibold text-gray-900">Todos</h1>
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-800">Sign out</button>
        </form>
    </header>

    <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar: Lists --}}
        <aside class="w-56 bg-white border-r border-gray-200 flex flex-col shrink-0">
            <div class="flex-1 overflow-y-auto p-3 space-y-1">
                <template x-for="list in lists" :key="list.id">
                    <div class="group flex items-center gap-1 rounded-lg px-2 py-1.5 cursor-pointer"
                         :class="currentListId === list.id ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-100 text-gray-700'"
                         @click="selectList(list.id)">
                        <template x-if="editingList?.id !== list.id">
                            <span class="flex-1 text-sm truncate" x-text="list.name"></span>
                        </template>
                        <template x-if="editingList?.id === list.id">
                            <input
                                x-model="editingList.name"
                                @blur="saveListRename()"
                                @keydown.enter="saveListRename()"
                                @keydown.escape="editingList = null"
                                @click.stop
                                class="flex-1 text-sm bg-transparent border-b border-blue-500 outline-none"
                                x-ref="renameInput"
                            >
                        </template>
                        <button @click.stop="startRename(list)"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 p-0.5 rounded"
                                title="Rename">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                            </svg>
                        </button>
                        <button @click.stop="deleteList(list)"
                                x-show="list.name !== 'Inbox'"
                                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 p-0.5 rounded"
                                title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- New list form --}}
            <div class="p-3 border-t border-gray-100">
                <form @submit.prevent="createList()" class="flex gap-1">
                    <input
                        x-model="newListName"
                        placeholder="New list…"
                        class="flex-1 text-sm border border-gray-200 rounded-md px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-400"
                    >
                    <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium px-1">Add</button>
                </form>
            </div>
        </aside>

        {{-- Main: Todos --}}
        <main class="flex-1 flex flex-col overflow-hidden">
            <template x-if="currentListId">
                <div class="flex-1 flex flex-col overflow-hidden">

                    {{-- Todo list --}}
                    <ul class="flex-1 overflow-y-auto p-4 space-y-2">
                        <template x-for="todo in currentTodos" :key="todo.id">
                            <li class="group flex items-center gap-3 bg-white rounded-xl px-4 py-3 shadow-xs border border-gray-100 transition-opacity"
                                draggable="true"
                                :class="draggingId === todo.id ? 'opacity-40' : 'opacity-100'"
                                @dragstart="dragStart(todo)"
                                @dragover.prevent="dragOver(todo)"
                                @dragend="dragEnd()">
                                {{-- Drag handle --}}
                                <span class="opacity-0 group-hover:opacity-40 cursor-grab active:cursor-grabbing text-gray-400 shrink-0" draggable="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 2a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zM7 8a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zM7 14a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4z"/>
                                    </svg>
                                </span>
                                <input
                                    type="checkbox"
                                    :checked="todo.done"
                                    @change="toggleDone(todo)"
                                    class="rounded text-blue-600"
                                >
                                <span class="flex-1 text-sm text-gray-800" :class="todo.done ? 'line-through text-gray-400' : ''" x-text="todo.title"></span>

                                {{-- Move to list --}}
                                <select
                                    @change="moveTodo(todo, $event.target.value); $event.target.value = ''"
                                    class="hidden group-hover:block text-xs border border-gray-200 rounded px-1 py-0.5 bg-white text-gray-600"
                                >
                                    <option value="">Move…</option>
                                    <template x-for="list in lists.filter(l => l.id !== currentListId)" :key="list.id">
                                        <option :value="list.id" x-text="list.name"></option>
                                    </template>
                                </select>

                                <button @click="deleteTodo(todo)"
                                        class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 p-1 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </li>
                        </template>
                        <li x-show="currentTodos.length === 0" class="text-sm text-gray-400 text-center py-8">No todos yet</li>
                    </ul>

                    {{-- New todo form --}}
                    <div class="p-4 bg-white border-t border-gray-200">
                        <form @submit.prevent="createTodo()" class="flex gap-2">
                            <input
                                x-model="newTodoTitle"
                                placeholder="Add a todo…"
                                class="flex-1 border border-gray-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                            >
                            <button type="submit" class="bg-blue-600 text-white rounded-xl px-4 py-2 text-sm font-medium hover:bg-blue-700 transition-colors">Add</button>
                        </form>
                    </div>
                </div>
            </template>
            <template x-if="!currentListId">
                <div class="flex-1 flex items-center justify-center text-sm text-gray-400">Select a list</div>
            </template>
        </main>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
