<?php

namespace Database\Seeders;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\CannedReply;
use App\Models\Client;
use App\Models\RequestComment;
use App\Models\RequestFile;
use App\Models\User;
use App\Models\Firm;
use App\Models\WorkRequest;
use App\Support\ActivityLogger;
use App\Support\PortalNotifier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoPortalSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $firm = Firm::create([
                'name' => 'ClientLane Accounting',
                'slug' => 'clientlane-demo',
                'niche' => 'Accounting & bookkeeping',
                'portal_tagline' => 'Collect files, track requests, and cut status-check emails.',
                'primary_color' => '#C35A1C',
            ]);

            $admin = User::create([
                'firm_id' => $firm->id,
                'name' => 'Avery Sloan',
                'email' => 'admin@clientlane.test',
                'title' => 'Managing Partner',
                'role' => UserRole::Staff->value,
                'password' => 'password',
                'email_verified_at' => now(),
            ]);

            $ops = User::create([
                'firm_id' => $firm->id,
                'name' => 'Mila Reyes',
                'email' => 'ops@clientlane.test',
                'title' => 'Operations Lead',
                'role' => UserRole::Staff->value,
                'password' => 'password',
                'email_verified_at' => now(),
            ]);

            $bakery = $this->createClient($firm, 'Harbor Bakery LLC', 'Mina Patel', 'mina@harborbakery.test');
            $agency = $this->createClient($firm, 'Redwood Creative', 'Leo Grant', 'leo@redwoodcreative.test');
            $hvac = $this->createClient($firm, 'Northwind HVAC', 'Dana Brooks', 'dana@northwindhvac.test');

            $bakeryRequest = WorkRequest::create([
                'firm_id' => $firm->id,
                'client_id' => $bakery->id,
                'submitted_by_user_id' => $bakery->user->id,
                'assigned_to_user_id' => $ops->id,
                'title' => 'March bookkeeping packet',
                'request_type' => 'Monthly close',
                'summary' => 'Need the final March bank statement, missing vendor invoices, and payroll register before close.',
                'status' => RequestStatus::WaitingOnClient->value,
                'priority' => RequestPriority::High->value,
                'due_at' => now()->addDays(2),
            ]);

            $agencyRequest = WorkRequest::create([
                'firm_id' => $firm->id,
                'client_id' => $agency->id,
                'submitted_by_user_id' => $agency->user->id,
                'assigned_to_user_id' => $admin->id,
                'title' => 'Q1 sales tax review',
                'request_type' => 'Sales tax filing',
                'summary' => 'Review taxable sales by state and confirm exemption certificates before filing.',
                'status' => RequestStatus::InProgress->value,
                'priority' => RequestPriority::Normal->value,
                'due_at' => now()->addDays(5),
            ]);

            $hvacRequest = WorkRequest::create([
                'firm_id' => $firm->id,
                'client_id' => $hvac->id,
                'submitted_by_user_id' => $hvac->user->id,
                'assigned_to_user_id' => $ops->id,
                'title' => 'Payroll corrections for field team',
                'request_type' => 'Payroll adjustment',
                'summary' => 'Three technicians were coded to the wrong job class on the last run and need corrected stubs.',
                'status' => RequestStatus::WaitingOnStaff->value,
                'priority' => RequestPriority::Urgent->value,
                'due_at' => now()->addDay(),
            ]);

            $completedRequest = WorkRequest::create([
                'firm_id' => $firm->id,
                'client_id' => $bakery->id,
                'submitted_by_user_id' => $admin->id,
                'assigned_to_user_id' => $admin->id,
                'title' => 'Certificate of insurance request',
                'request_type' => 'Compliance document',
                'summary' => 'Provide the updated certificate of insurance for the landlord renewal packet.',
                'status' => RequestStatus::Completed->value,
                'priority' => RequestPriority::Low->value,
                'due_at' => now()->subDays(1),
                'completed_at' => now()->subDay(),
            ]);

            $this->seedComments($bakeryRequest, $ops, $bakery->user, [
                ['author' => $ops, 'body' => 'We are still missing the March 31 bank statement and two vendor bills.', 'internal' => false],
                ['author' => $ops, 'body' => 'Client tends to upload the bank statement after noon on Fridays.', 'internal' => true],
            ]);

            $this->seedComments($agencyRequest, $admin, $agency->user, [
                ['author' => $agency->user, 'body' => 'We uploaded the new exemption certificate from Oregon this morning.', 'internal' => false],
                ['author' => $admin, 'body' => 'Received. Filing review is in progress and we will confirm by tomorrow.', 'internal' => false],
            ]);

            $this->seedComments($hvacRequest, $ops, $hvac->user, [
                ['author' => $hvac->user, 'body' => 'Adding payroll screenshots here so your team can match the corrected hours.', 'internal' => false],
            ]);

            $this->attachTextFile($bakeryRequest, $bakery->user, 'march-missing-docs.txt', "Missing items:\n- March 31 bank statement\n- Fresh Bakes invoice\n- Midtown Utilities invoice\n");
            $this->attachTextFile($agencyRequest, $agency->user, 'oregon-exemption-certificate.txt', "Oregon exemption certificate received on {$agencyRequest->created_at?->toDateString()}.\n");
            $this->attachTextFile($hvacRequest, $hvac->user, 'payroll-corrections.txt', "Technicians affected:\n- Carlos N.\n- Mia T.\n- Ben R.\n");

            CannedReply::insert([
                [
                    'firm_id' => $firm->id,
                    'title' => 'Missing document reminder',
                    'category' => 'Reminders',
                    'target_status' => RequestStatus::WaitingOnClient->value,
                    'content' => "We’re still waiting on the outstanding files for this request. Please upload them in the portal so we can keep the deadline on track.",
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'firm_id' => $firm->id,
                    'title' => 'Status update in progress',
                    'category' => 'Updates',
                    'target_status' => RequestStatus::InProgress->value,
                    'content' => 'Your request is in progress with our team. We will post the next update here as soon as review is complete.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'firm_id' => $firm->id,
                    'title' => 'Completion handoff',
                    'category' => 'Closures',
                    'target_status' => RequestStatus::Completed->value,
                    'content' => 'This request is complete. Please review the attached deliverables and reply in the thread if anything still needs adjustment.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            ActivityLogger::forFirm($firm, $admin, 'workspace.seeded', 'Loaded demo workspace data.', []);

            PortalNotifier::send(
                [$ops],
                'Waiting on client documents',
                "Harbor Bakery still owes files for \"{$bakeryRequest->title}\".",
                PortalNotifier::requestUrl($bakeryRequest),
                'warning'
            );

            PortalNotifier::send(
                [$bakery->user],
                'Reminder to upload files',
                "Please upload the remaining documents for \"{$bakeryRequest->title}\".",
                PortalNotifier::requestUrl($bakeryRequest),
                'warning'
            );
        });
    }

    private function createClient(Firm $firm, string $companyName, string $contactName, string $email): Client
    {
        $client = Client::create([
            'firm_id' => $firm->id,
            'company_name' => $companyName,
            'primary_contact_name' => $contactName,
            'email' => $email,
            'phone' => '+1 555-0100',
            'notes' => 'Imported from the beta pilot intake spreadsheet.',
            'is_active' => true,
        ]);

        User::create([
            'firm_id' => $firm->id,
            'client_id' => $client->id,
            'name' => $contactName,
            'email' => $email,
            'title' => 'Primary client contact',
            'role' => UserRole::Client->value,
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        return $client->load('user');
    }

    private function seedComments(WorkRequest $workRequest, User $staffUser, User $clientUser, array $items): void
    {
        foreach ($items as $item) {
            $comment = RequestComment::create([
                'work_request_id' => $workRequest->id,
                'user_id' => $item['author']->id,
                'body' => $item['body'],
                'is_internal' => $item['internal'],
            ]);

            ActivityLogger::forRequest(
                $workRequest,
                $item['author'],
                'comment.seeded',
                $item['internal'] ? 'Seeded internal note.' : 'Seeded client-visible reply.',
                ['comment_id' => $comment->id]
            );
        }
    }

    private function attachTextFile(WorkRequest $workRequest, User $user, string $fileName, string $contents): void
    {
        $storedName = Str::uuid()->toString().'_'.$fileName;
        $path = "work-requests/{$workRequest->id}/{$storedName}";

        Storage::disk('local')->put($path, $contents);

        $file = RequestFile::create([
            'work_request_id' => $workRequest->id,
            'user_id' => $user->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $fileName,
            'stored_name' => $storedName,
            'mime_type' => 'text/plain',
            'size_bytes' => strlen($contents),
        ]);

        ActivityLogger::forRequest(
            $workRequest,
            $user,
            'file.seeded',
            "Seeded file {$file->original_name}.",
            ['file_id' => $file->id]
        );
    }
}
