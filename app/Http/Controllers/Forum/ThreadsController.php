<?php

namespace Lio\Http\Controllers\Forum;

use Gate;
use Lio\Forum\Thread;
use Lio\Forum\ThreadRequest;
use Lio\Http\Controllers\Controller;
use Lio\Forum\ThreadRepository;
use Lio\Tags\TagRepository;

class ThreadsController extends Controller
{
    /**
     * @var \Lio\Forum\ThreadRepository
     */
    private $threads;

    public function __construct(ThreadRepository $threads)
    {
        $this->threads = $threads;

        $this->middleware('auth', ['except' => ['overview', 'show']]);
    }

    public function overview()
    {
        return view('forum.overview', ['threads' => $this->threads->findAllPaginated()]);
    }

    public function show(Thread $thread)
    {
        return view('forum.threads.show', compact('thread'));
    }

    public function create(TagRepository $tags)
    {
        return view('forum.threads.create', ['tags' => $tags->findAll()]);
    }

    public function store(ThreadRequest $request)
    {
        $thread = $this->threads->create(auth()->user(), $request->get('subject'), $request->get('body'), $request->only('tags'));

        return redirect()->route('thread', $thread->slug());
    }

    public function edit(TagRepository $tags, Thread $thread)
    {
        $this->authorize('update', $thread);

        return view('forum.threads.edit', [
            'thread' => $thread,
            'tags' => $tags->findAll(),
        ]);
    }

    public function update(ThreadRequest $request, Thread $thread)
    {
        $this->authorize('update', $thread);

        $this->threads->update($thread, $request->only('subject', 'body', 'tags'));

        return redirect()->route('thread', $thread->slug());
    }

    public function delete(Thread $thread)
    {
        $this->authorize('delete', $thread);

        $this->threads->delete($thread);

        return redirect()->route('forum');
    }
}