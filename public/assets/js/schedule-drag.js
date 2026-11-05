function initScheduleDrag(gridDate, openingHour, spanMinutes) {
    const SPAN_MINUTES = spanMinutes;
    const SNAP_MINUTES = 5;
    const MAGNET_MINUTES = 10; // snap flush against a neighbor's edge when within this many minutes

    // `drag` holds the state of the drag in progress (which block, where the mouse started...),
    // or null when nothing is being dragged. mousedown fills it, mousemove uses it, mouseup clears it.
    let drag = null;
    // After a drag, the browser also fires a click on the block. This remembers the block so
    // that click can be swallowed instead of opening the edit form.
    let justDragged = null;

    document.addEventListener('click', function (event) {
        if (justDragged && event.target.closest('.schedule-board__showing') === justDragged) {
            event.preventDefault();
            justDragged = null;
        }
    }, true);

    // Two time ranges overlap if each one starts before the other ends.
    function overlaps(aStart, aEnd, bStart, bEnd) {
        return aStart < bEnd && aEnd > bStart;
    }

    // Snaps a dragged start time to a neighbor's start/end edge when close, so two showings can
    // be lined up with zero gap even if the neighbor's duration isn't a multiple of SNAP_MINUTES.
    function magnetSnap(timeline, block, minutes, duration) {
        const others = timeline.querySelectorAll('.schedule-board__showing');
        let best = minutes;
        let bestDistance = MAGNET_MINUTES;

        for (const other of others) {
            if (other === block) {
                continue;
            }
            const otherStart = Number(other.dataset.minutes);
            const otherEnd = otherStart + Number(other.dataset.duration);

            const afterOther = otherEnd;
            if (Math.abs(minutes - afterOther) < bestDistance) {
                best = afterOther;
                bestDistance = Math.abs(minutes - afterOther);
            }

            const beforeOther = otherStart - duration;
            if (Math.abs(minutes - beforeOther) < bestDistance) {
                best = beforeOther;
                bestDistance = Math.abs(minutes - beforeOther);
            }
        }

        return best;
    }

    // Would the block be allowed here? No overlap with any other block on the row, and
    // entirely inside the grid (not before opening, not past closing).
    function isDropValid(timeline, block, minutes, duration) {
        const others = timeline.querySelectorAll('.schedule-board__showing');
        const end = minutes + duration;

        for (const other of others) {
            if (other === block) {
                continue;
            }
            const otherStart = Number(other.dataset.minutes);
            const otherEnd = otherStart + Number(other.dataset.duration);
            if (overlaps(minutes, end, otherStart, otherEnd)) {
                return false;
            }
        }

        return minutes >= 0 && end <= SPAN_MINUTES;
    }

    // Same maths as PHP's timelineStyle(): position and width as a percentage of the row.
    function applyPosition(block, timeline, minutes, duration) {
        const left = minutes / SPAN_MINUTES * 100;
        const width = duration / SPAN_MINUTES * 100;
        block.style.left = left + '%';
        block.style.width = width + '%';
    }

    // Minutes since opening -> "2026-10-31 20:05:00", the format the database column expects.
    // padStart(2, '0') turns 5 into "05".
    function minutesToDatetime(minutes) {
        const totalMinutes = openingHour * 60 + minutes;
        const hh = String(Math.floor(totalMinutes / 60) % 24).padStart(2, '0');
        const mm = String(totalMinutes % 60).padStart(2, '0');
        return `${gridDate} ${hh}:${mm}:00`;
    }

    // Attach the mouse handlers to every block on the grid.
    document.querySelectorAll('.schedule-board__showing').forEach(block => {
        // Mouse pressed on a block: remember where the drag started. Nothing moves yet.
        // (button 0 = left mouse button; ignore presses on the duplicate "+" button.)
        block.addEventListener('mousedown', function (event) {
            if (event.button !== 0 || event.target.closest('.schedule-board__duplicate')) {
                return;
            }

            drag = {
                block,
                startX: event.clientX,
                startMinutes: Number(block.dataset.minutes),
                duration: Number(block.dataset.duration),
                timeline: block.closest('.schedule-board__timeline'),
                moved: false,
            };
        });

        // Block is a plain div now (not a link), so clicking it has to navigate manually — unless
        // the click was really the tail end of a drag (already preventDefault'd above) or landed
        // on the duplicate button.
        block.addEventListener('click', function (event) {
            if (event.defaultPrevented || event.target.closest('.schedule-board__duplicate')) {
                return;
            }
            window.location.href = block.dataset.editHref;
        });

        const duplicateButton = block.querySelector('.schedule-board__duplicate');
        duplicateButton.addEventListener('click', function (event) {
            // stopPropagation: don't let this click also count as a click on the block (which would open the editor).
            event.stopPropagation();

            // New start = right where this block ends, but never past the end of the grid.
            const timeline = block.closest('.schedule-board__timeline');
            const duration = Number(block.dataset.duration);
            const minutes = Math.min(Number(block.dataset.minutes) + duration, SPAN_MINUTES - duration);

            // URLSearchParams builds "action=duplicate&movie_id=5&...", the same format a normal
            // form submission uses, so PHP reads it from $_POST exactly like a form.
            const body = new URLSearchParams({
                action: 'duplicate',
                movie_id: block.dataset.movieId,
                screen_id: timeline.dataset.screenId,
                show_datetime: minutesToDatetime(minutes),
            });

            fetch(window.location.pathname + window.location.search, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body,
            })
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        window.location.reload();
                        return;
                    }
                    alert(data.error || 'No room right after this showing — drag it to a free slot instead.');
                })
                .catch(() => alert('Could not reach the server.'));
        });
    });

    // Mouse moving anywhere on the page while a drag is active: move the block with it.
    document.addEventListener('mousemove', function (event) {
        if (!drag) {
            return;
        }

        // Ignore tiny movements (< 4 pixels) so an ordinary click isn't mistaken for a drag.
        const deltaX = event.clientX - drag.startX;
        if (!drag.moved && Math.abs(deltaX) < 4) {
            return;
        }
        drag.moved = true;
        event.preventDefault();
        drag.block.classList.add('is-dragging');

        // Pixels moved -> minutes moved (the row's full width equals the whole opening span),
        // then round to the nearest 5 minutes, pull flush against a neighbour if close, and
        // keep the block inside the grid.
        const rowWidth = drag.timeline.getBoundingClientRect().width;
        const deltaMinutes = deltaX / rowWidth * SPAN_MINUTES;
        let minutes = Math.round((drag.startMinutes + deltaMinutes) / SNAP_MINUTES) * SNAP_MINUTES;
        minutes = magnetSnap(drag.timeline, drag.block, minutes, drag.duration);
        minutes = Math.max(0, Math.min(SPAN_MINUTES - drag.duration, minutes));

        drag.pendingMinutes = minutes;
        applyPosition(drag.block, drag.timeline, minutes, drag.duration);

        // Red tint while hovering over an invalid spot.
        const valid = isDropValid(drag.timeline, drag.block, minutes, drag.duration);
        drag.block.classList.toggle('is-invalid-drop', !valid);
    });

    // Mouse released: decide what to do with the drag.
    document.addEventListener('mouseup', function () {
        if (!drag) {
            return;
        }

        // `const { a, b } = drag` copies those named fields out of the drag object into variables.
        const { block, moved, timeline, startMinutes, duration } = drag;
        block.classList.remove('is-dragging');

        // Never actually moved: it was a click, not a drag. Let the click handler open the editor.
        if (!moved) {
            drag = null;
            return;
        }

        justDragged = block;

        // Dropped on an invalid spot: slide back to where it started, save nothing.
        const minutes = drag.pendingMinutes;
        const valid = isDropValid(timeline, block, minutes, duration);
        block.classList.remove('is-invalid-drop');

        if (!valid) {
            applyPosition(block, timeline, startMinutes, duration);
            drag = null;
            return;
        }

        // Dropped exactly where it was: nothing to save.
        if (minutes === startMinutes) {
            drag = null;
            return;
        }

        // Valid new time: tell the server (see file header for how this round-trip works).
        const body = new URLSearchParams({
            action: 'move',
            id: block.dataset.id,
            movie_id: block.dataset.movieId,
            screen_id: timeline.dataset.screenId,
            show_datetime: minutesToDatetime(minutes),
        });

        fetch(window.location.pathname + window.location.search, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body,
        })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    window.location.reload();
                    return;
                }
                alert(data.error || 'Could not move this showing.');
                applyPosition(block, timeline, startMinutes, duration);
            })
            .catch(() => {
                alert('Could not reach the server.');
                applyPosition(block, timeline, startMinutes, duration);
            });

        drag = null;
    });
}
