<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TeacherTimetableMail extends Mailable
{
    public function __construct(
        public string $teacherName,
        public string $schoolYear,
        public array $days,
        public array $periods,
        public array $grid,
    ) {
    }

    public function build()
    {
        return $this->subject("Emploi du temps - {$this->schoolYear}")
            ->view('emails.teacher-timetable');
    }
}
