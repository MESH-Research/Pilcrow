<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Enums\SubmissionFileImportStatus;
use App\Exceptions\ClientException;
use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\SubmissionFile;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Pandoc\Facades\Pandoc;

/**
 * Intent-shaped lifecycle/content mutations on a submission.
 *
 * Grouped controller-style (resolved via `Class@method`, the house convention):
 * each method is one business action that replaces a slice of the deprecated
 * `updateSubmission` god-mutation. Authorization is enforced by the @canFind
 * directive on each field, so by the time a method runs the act is permitted.
 * Failures surface as {@see \App\Exceptions\ClientException} with a stable code,
 * consistent with the sibling comment mutations.
 */
final readonly class SubmissionMutator
{
    /**
     * Update the author's work — body and/or title — on a submission. The
     * `updateContent` ability (author-only, draft-only) authorizes both; the
     * validator guarantees at least one is supplied. A new SubmissionContent
     * version is only created when a body is given, so a title-only edit does
     * not churn content.
     *
     * @param null $_
     * @param array{id: string, content?: string, title?: string} $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function updateContent(null $_, array $args): Submission
    {
        try {
            $submission = Submission::findOrFail($args['id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'updateSubmissionContent', 'SUBMISSION_NOT_FOUND');
        }

        if (array_key_exists('content', $args) && $args['content'] !== null) {
            $content = new SubmissionContent();
            $content->data = $args['content'];
            $content->submission_id = $submission->id;

            if (! $content->save()) {
                throw new ClientException('Error', 'updateSubmissionContent', 'UNABLE_TO_SAVE_CONTENT');
            }
            $submission->content_id = $content->id;
        }

        if (array_key_exists('title', $args) && $args['title'] !== null) {
            $submission->title = $args['title'];
        }

        if (! $submission->save()) {
            throw new ClientException('Error', 'updateSubmissionContent', 'UNABLE_TO_SAVE_SUBMISSION');
        }

        return $submission;
    }

    /**
     * Send a draft submission in for review.
     *
     * The submitter holds `submit` only while DRAFT, so by the time we run the
     * submission is a draft the acting user may submit.
     *
     * @param null $_
     * @param array{submission_id: string} $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function submit(null $_, array $args): Submission
    {
        try {
            $submission = Submission::findOrFail($args['submission_id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'submitSubmission', 'SUBMISSION_NOT_FOUND');
        }

        $submission->status = Submission::INITIALLY_SUBMITTED;
        $submission->save();

        return $submission;
    }

    /**
     * Replace the author's work from an uploaded file: store the upload, convert
     * it to HTML with Pandoc, and save it as a new SubmissionContent version.
     * Gated by the same `updateContent` ability as the text path.
     *
     * @param null $_
     * @param array{submission_id: string, file_upload: \Illuminate\Http\UploadedFile} $args
     * @return \App\Models\Submission
     * @throws \App\Exceptions\ClientException
     */
    public function updateContentWithFile(null $_, array $args): Submission
    {
        try {
            $submission = Submission::findOrFail($args['submission_id']);
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'updateSubmissionContentWithFile', 'SUBMISSION_NOT_FOUND');
        }

        $content = new SubmissionContent();
        $content->submission_id = $submission->id;

        $file = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_upload' => $args['file_upload']->storePublicly('uploads'),
            'content_id' => $content->id,
        ]);

        try {
            $content->data = Pandoc::inputFile(storage_path('app/' . $file->file_upload))
                ->noStandalone()
                ->to('html')
                ->run();
        } catch (Exception $e) {
            throw new ClientException('Error', 'updateSubmissionContentWithFile', 'UNABLE_TO_CONVERT_FILE');
        }

        $file->import_status = SubmissionFileImportStatus::Success;
        if (! $file->save()) {
            throw new ClientException('Error', 'updateSubmissionContentWithFile', 'UNABLE_TO_SAVE_FILE');
        }

        $content->submission_file_id = $file->id;
        if (! $content->save()) {
            throw new ClientException('Error', 'updateSubmissionContentWithFile', 'UNABLE_TO_SAVE_CONTENT');
        }

        $submission->content_id = $content->id;
        if (! $submission->save()) {
            throw new ClientException('Error', 'updateSubmissionContentWithFile', 'UNABLE_TO_SAVE_SUBMISSION');
        }

        return $submission;
    }
}
