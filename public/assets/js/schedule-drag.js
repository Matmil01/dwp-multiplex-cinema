// Drag-to-reschedule for the admin schedule grid: drag a block along its own row to change the time
// (snapped to 5 min, magnetic against neighbor edges). Screen stays fixed — a print is booked for a
// specific screen/format, so dragging a showing onto another row isn't allowed. Overlap is checked
// client-side against the other blocks on the row before we ever hit the server/DB trigger. Called
// once per page load with the grid's date (see schedule.php).
function initScheduleDrag(gridDate) {
    const SPAN_MINUTES = 12 * 60; // grid covers 12:00-23:59
    const SNAP_MINUTES = 5;
    const MAGNET_MINUTES = 10; // snap flush against a neighbor's edge when within this many minutes

    let drag = null;
    let justDragged = null;

    document.addEventListener('click', function (event) {
        if (justDragged && event.target.closest('.schedule-board__showing') === justDragged) {
            event.preventDefault();
            justDragged = null;
        }
    }, true);

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

    function applyPosition(block, timeline, minutes, duration) {
        const left = minutes / SPAN_MINUTES * 100;
        const width = duration / SPAN_MINUTES * 100;
        block.style.left = left + '%';
        block.style.width = width + '%';
    }

    function minutesToDatetime(minutes) {
        const totalMinutes = 12 * 60 + minutes;
        const hh = String(Math.floor(totalMinutes / 60) % 24).padStart(2, '0');
        const mm = String(totalMinutes % 60).padStart(2, '0');
        return `${gridDate} ${hh}:${mm}:00`;
    }

    document.querySelectorAll('.schedule-board__showing').forEach(block => {
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
            event.stopPropagation();

            const timeline = block.closest('.schedule-board__timeline');
            const duration = Number(block.dataset.duration);
            const minutes = Math.min(Number(block.dataset.minutes) + duration, SPAN_MINUTES - duration);

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

    document.addEventListener('mousemove', function (event) {
        if (!drag) {
            return;
        }

        const deltaX = event.clientX - drag.startX;
        if (!drag.moved && Math.abs(deltaX) < 4) {
            return;
        }
        drag.moved = true;
        event.preventDefault();
        drag.block.classList.add('is-dragging');

        const rowWidth = drag.timeline.getBoundingClientRect().width;
        const deltaMinutes = deltaX / rowWidth * SPAN_MINUTES;
        let minutes = Math.round((drag.startMinutes + deltaMinutes) / SNAP_MINUTES) * SNAP_MINUTES;
        minutes = magnetSnap(drag.timeline, drag.block, minutes, drag.duration);
        minutes = Math.max(0, Math.min(SPAN_MINUTES - drag.duration, minutes));

        drag.pendingMinutes = minutes;
        applyPosition(drag.block, drag.timeline, minutes, drag.duration);

        const valid = isDropValid(drag.timeline, drag.block, minutes, drag.duration);
        drag.block.classList.toggle('is-invalid-drop', !valid);
    });

    document.addEventListener('mouseup', function () {
        if (!drag) {
            return;
        }

        const { block, moved, timeline, startMinutes, duration } = drag;
        block.classList.remove('is-dragging');

        if (!moved) {
            drag = null;
            return;
        }

        justDragged = block;

        const minutes = drag.pendingMinutes;
        const valid = isDropValid(timeline, block, minutes, duration);
        block.classList.remove('is-invalid-drop');

        if (!valid) {
            applyPosition(block, timeline, startMinutes, duration);
            drag = null;
            return;
        }

        if (minutes === startMinutes) {
            drag = null;
            return;
        }

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
