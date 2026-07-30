<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;
class Controller extends BaseController
{
    private const GOOGLE_ANDROID_PACKAGE_NAME = 'com.bacaan.myapp';
    private const APPLE_BUNDLE_ID = 'com.bacaan.myapp';
    // Fallback OAuth client ID kalau google-services.json tidak terbaca di server.
    // Berisi Android client com.bacaan.myapp dan Web client lama.
    private const GOOGLE_FALLBACK_ALLOWED_CLIENT_IDS = [
        '438568225178-odnn3gscknps1j4iudjju8434025jevj.apps.googleusercontent.com',
        '438568225178-okgo8agdj1e6ef21pt5vpqutm0o4gn74.apps.googleusercontent.com',
        '438568225178-pql53tjm7iul6ar54rf3fvct79p1sasf.apps.googleusercontent.com',
    ];
    private const DAILY_REWARD_SCHEDULE_TABLE_CANDIDATES = [
        'mst_daily_reward_video_schedules',
        'mst_daily_reward_schedules',
        'mst_daily_rewards_schedule',
        'mst_daily_rewards_schedules',
        'mst_daily_rewatds_schedules',
    ];
    private const DAILY_REWARD_VIDEO_TABLE_CANDIDATES = [
        'mst_reward_videos',
        'mst_daily_reward_videos',
        'mst_daily_rewards_videos',
        'mst_daily_reward_video',
        'mst_reward_video',
        'mst_videos',
        'mst_video',
    ];
    private const DAILY_REWARD_VIDEO_URL_COLUMN_CANDIDATES = [
        'video_url',
        'video_path',
        'target_url',
        'media_url',
        'url_video',
        'url',
        'file_url',
        'video_file',
        'file',
        'path',
        'video',
        'link',
    ];
    private const DAILY_REWARD_VIDEO_TITLE_COLUMN_CANDIDATES = [
        'title',
        'nama_video',
        'name',
        'nama',
        'judul',
    ];
    public function getKategori()
    {
        try {
            $data = DB::table('mst_kategori')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data kategori.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getDailyRewardsByUser($user_id = null)
    {
        if ($user_id !== null && (!filter_var($user_id, FILTER_VALIDATE_INT) || (int) $user_id < 1)) {
            return response()->json([
                'status' => 'error',
                'message' => 'user_id tidak valid.'
            ], 422);
        }
        $userId = $user_id ? (int) $user_id : null;
        try {
            $today = date('Y-m-d');
            $todayDayOfWeek = (int) date('N');
            $scheduleTable = $this->getDailyRewardScheduleTable();
            $videoTable = $scheduleTable ? $this->getDailyRewardVideoTable() : null;
            $scheduleHasRewardVideoId = $scheduleTable ? $this->tableHasColumn($scheduleTable, 'reward_video_id') : false;
            $claimTableHasRewardVideoId = $this->tableHasColumn('mst_daily_reward_claims', 'reward_video_id');
            $claimTableHasClaimKey = $this->tableHasColumn('mst_daily_reward_claims', 'claim_key');
            $videoUrlColumns = $videoTable ? $this->getExistingColumns($videoTable, self::DAILY_REWARD_VIDEO_URL_COLUMN_CANDIDATES) : [];
            $videoTitleColumn = $videoTable ? $this->getFirstExistingColumn($videoTable, self::DAILY_REWARD_VIDEO_TITLE_COLUMN_CANDIDATES) : null;
            $query = DB::table('mst_daily_rewards as dr')
                ->where('dr.status', true);

            if ($scheduleTable) {
                $query->leftJoin("{$scheduleTable} as drs", function ($join) use ($todayDayOfWeek) {
                    $join->on('dr.id', '=', 'drs.daily_reward_id')
                         ->where('drs.day_of_week', '=', $todayDayOfWeek);
                })->where(function ($query) {
                    $query->where('dr.reward_type_id', '<>', 2)
                          ->orWhereNotNull('drs.id');
                });

                if ($videoTable && $scheduleHasRewardVideoId) {
                    $query->leftJoin("{$videoTable} as drv", 'drs.reward_video_id', '=', 'drv.id');
                }
            } else {
                $query->where('dr.reward_type_id', '<>', 2);
            }

            $scheduleSelects = $scheduleTable
                ? [
                    'drs.id as daily_reward_schedule_id',
                    $scheduleHasRewardVideoId ? 'drs.reward_video_id' : DB::raw('null as reward_video_id'),
                    'drs.day_of_week',
                ]
                : [
                    DB::raw('null as daily_reward_schedule_id'),
                    DB::raw('null as reward_video_id'),
                    DB::raw('null as day_of_week'),
            ];
            $videoSelects = [];
            if ($videoTable && $scheduleHasRewardVideoId && count($videoUrlColumns) > 0) {
                $videoUrlExpressions = [];
                foreach ($videoUrlColumns as $videoUrlColumn) {
                    $videoUrlExpressions[] = "NULLIF(drv." . $this->quoteIdentifier($videoUrlColumn) . "::text, '')";
                }
                $videoUrlExpression = count($videoUrlExpressions) === 1
                    ? $videoUrlExpressions[0]
                    : 'COALESCE(' . implode(', ', $videoUrlExpressions) . ')';
                $videoSelects[] = DB::raw("{$videoUrlExpression} as daily_reward_video_url");
            } else {
                $videoSelects[] = DB::raw('null as daily_reward_video_url');
            }
            if ($videoTable && $scheduleHasRewardVideoId && $videoTitleColumn) {
                $videoSelects[] = DB::raw("drv." . $this->quoteIdentifier($videoTitleColumn) . "::text as daily_reward_video_title");
            } else {
                $videoSelects[] = DB::raw('null as daily_reward_video_title');
            }

            if ($userId) {
                $query->select(array_merge(
                    ['dr.*'],
                    $scheduleSelects,
                    $videoSelects,
                    [
                    DB::raw('false as is_claimed')
                    ]
                ));
            } else {
                $query->select(array_merge(
                    ['dr.*'],
                    $scheduleSelects,
                    $videoSelects,
                    [
                    DB::raw('false as is_claimed')
                    ]
                ));
            }
            $query->orderBy('dr.id', 'asc');
            if ($scheduleTable) {
                $query->orderBy('drs.id', 'asc');
            }
            $data = $query->get();
            $claimedDailyRewardIds = [];
            $claimedVideoIdsByReward = [];
            $claimedClaimKeysByReward = [];

            if ($userId) {
                $claimSelects = ['daily_reward_id'];
                if ($claimTableHasRewardVideoId) {
                    $claimSelects[] = 'reward_video_id';
                }
                if ($claimTableHasClaimKey) {
                    $claimSelects[] = 'claim_key';
                }

                $claimRows = DB::table('mst_daily_reward_claims')
                    ->where('user_id', $userId)
                    ->where('claim_date', $today)
                    ->select($claimSelects)
                    ->get();

                foreach ($claimRows as $claim) {
                    $dailyRewardId = (int) $claim->daily_reward_id;
                    $claimedDailyRewardIds[$dailyRewardId] = true;

                    if ($claimTableHasRewardVideoId) {
                        $rewardVideoId = (int) ($claim->reward_video_id ?? 0);
                        if ($rewardVideoId > 0) {
                            $claimedVideoIdsByReward[$dailyRewardId][$rewardVideoId] = true;
                        }
                    }

                    if ($claimTableHasClaimKey && !empty($claim->claim_key)) {
                        $claimedClaimKeysByReward[$dailyRewardId][(string) $claim->claim_key] = true;
                    }
                }
            }

            foreach ($data as $reward) {
                $dailyRewardId = (int) ($reward->id ?? 0);
                $rewardVideoId = (int) ($reward->reward_video_id ?? 0);

                if ((int) ($reward->reward_type_id ?? 0) === 2 && $rewardVideoId > 0) {
                    if ($claimTableHasClaimKey) {
                        $reward->is_claimed = isset($claimedClaimKeysByReward[$dailyRewardId][$this->dailyRewardVideoClaimKey($rewardVideoId)]);
                    } elseif ($claimTableHasRewardVideoId) {
                        $reward->is_claimed = isset($claimedVideoIdsByReward[$dailyRewardId][$rewardVideoId]);
                    } else {
                        $reward->is_claimed = isset($claimedDailyRewardIds[$dailyRewardId]);
                    }
                } else {
                    $reward->is_claimed = isset($claimedDailyRewardIds[$dailyRewardId]);
                }

                if ((int) ($reward->reward_type_id ?? 0) === 2) {
                    $scheduledVideoUrl = !empty($reward->daily_reward_video_url)
                        ? $this->normalizeDailyRewardVideoUrl((string) $reward->daily_reward_video_url)
                        : null;
                    $fallbackVideoUrl = !empty($reward->target_url)
                        ? $this->normalizeDailyRewardVideoUrl((string) $reward->target_url)
                        : null;

                    if ($scheduledVideoUrl) {
                        $reward->target_url = $scheduledVideoUrl;
                        $reward->daily_reward_video_url = $scheduledVideoUrl;
                    } else {
                        $reward->daily_reward_video_url = null;
                        if ($fallbackVideoUrl) {
                            $reward->target_url = $fallbackVideoUrl;
                        }
                    }
                }
            }
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data daily rewards.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getCerita(Request $request)
    {
        try {
            $limit = $request->query('limit');
            $offset = $request->query('offset');
            $query = DB::table('mst_cerita')
                ->select('mst_cerita.*', 'mst_kategori.default_title', 'mst_cerita.positions_index as position_index')
                ->join('mst_kategori', 'mst_cerita.id_kategori', '=', 'mst_kategori.id');

            if ($limit !== null) {
                if (!filter_var($limit, FILTER_VALIDATE_INT) || (int) $limit < 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'limit tidak valid.'
                    ], 422);
                }

                $query->orderBy('mst_cerita.id', 'asc')->limit(min((int) $limit, 100));
            }

            if ($offset !== null) {
                if (!filter_var($offset, FILTER_VALIDATE_INT) || (int) $offset < 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'offset tidak valid.'
                    ], 422);
                }

                $query->offset((int) $offset);
            }

            $data = $query->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data cerita.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getCeritaById($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || (int) $id < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID cerita tidak valid.'
            ], 422);
        }

        try {
            $data = DB::table('mst_cerita')
                ->select('mst_cerita.*', 'mst_kategori.default_title', 'mst_cerita.positions_index as position_index')
                ->join('mst_kategori', 'mst_cerita.id_kategori', '=', 'mst_kategori.id')
                ->where('mst_cerita.id', (int) $id)
                ->first();
            if (!$data) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data cerita tidak ditemukan.'
                ], 404);
            }
            $data->chapters = $this->buildStoryChapters($data);
            return response()->json([
                'status' => 'success',
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail cerita.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getCeritaChapters($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || (int) $id < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID cerita tidak valid.'
            ], 422);
        }

        try {
            $story = DB::table('mst_cerita')
                ->select('id', 'judul', 'isi_cerita')
                ->where('id', (int) $id)
                ->first();
            if (!$story) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data cerita tidak ditemukan.'
                ], 404);
            }

            $chapters = $this->buildStoryChapters($story);
            return response()->json([
                'status' => 'success',
                'count' => count($chapters),
                'results' => [
                    'id' => (int) $story->id,
                    'cerita_id' => (int) $story->id,
                    'judul' => $story->judul,
                    'chapters' => $chapters,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data chapter cerita.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getAction()
    {
        try {
            $data = DB::table('mst_action')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data action.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getSliders()
    {
        try {
            $data = DB::table('mst_sliders')
                ->where('status', true)
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data sliders.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getSyaratketentuan()
    {
        try {
            $data = DB::table('mst_syarat_ketentuan')
                // ->where('status', true)
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getPusatbantuan()
    {
        try {
            $data = DB::table('mst_pusat_bantuan')
                // ->where('status', true)
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
     public function getKebijakanprivasi()
    {
        try {
            $data = DB::table('mst_kebijakan_privasi')
                // ->where('status', true)
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getFaq()
    {
        try {
            $data = DB::table('mst_faqs')
                // ->where('status', true)
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getSubskoin()
    {
        try {
            $data = DB::table('mst_subs_koin')
                ->where('tipe', 'koin')
                ->where('status', true)
                ->orderBy('id', 'asc')
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data produk koin.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getSubslangganan()
    {
        try {
            $data = DB::table('mst_subs')
                ->where('tipe', 'langganan')
                ->where('status', true)
                ->orderBy('id', 'asc')
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data produk langganan.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
     public function getNotifikasi()
    {
        try {
            $data = DB::table('mst_notifikasi')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data notifikasi.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getUser()
    {
        try {
            $data = DB::table('mst_users')
                ->select('id', 'name', 'email', 'google_id', 'google_avatar', 'email_verified_at', 'last_login_at', 'auth_provider', 'points', 'koin', 'created_at', 'updated_at')
                ->where(function ($query) {
                    $query->whereNull('deleted')->orWhere('deleted', '!=', 1);
                })
                ->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data user.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getUserById($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || (int) $id < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID user tidak valid.'
            ], 422);
        }
        try {
            $user = DB::table('mst_users')
                ->where('id', (int) $id)
                ->where(function ($query) {
                    $query->whereNull('deleted')->orWhere('deleted', '!=', 1);
                })
                ->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'results' => $this->formatUser($user)
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data user.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getProfile($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT) || (int) $id < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'ID user tidak valid.'
            ], 422);
        }
        try {
            $user = DB::table('mst_users')
                ->where('id', (int) $id)
                ->where(function ($query) {
                    $query->whereNull('deleted')->orWhere('deleted', '!=', 1);
                })
                ->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data profil tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'results' => $this->formatUser($user)
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data profil.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getRedeemOptions()
    {
        try {
            $data = DB::table('mst_redeem_point')->orderBy('amount_poin', 'asc')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil opsi penukaran poin.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getUnlockedChapters(Request $request)
    {
        $userId = $request->input('user_id');
        $storyId = $request->input('cerita_id');
        if (!filter_var($userId, FILTER_VALIDATE_INT) || !filter_var($storyId, FILTER_VALIDATE_INT)) {
            return response()->json(['status' => 'error', 'message' => 'user_id and cerita_id are required and must be integers.'], 422);
        }
        try {
            $unlockedChapterIds = DB::table('trx_unlock_chapter')
                ->where('user_id', (int)$userId)
                ->where('cerita_id', (int)$storyId)
                ->pluck('chapter_id');
            return response()->json([
                'status' => 'success',
                'results' => $unlockedChapterIds
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to fetch unlocked chapters.', 'error_detail' => $e->getMessage()], 500);
        }
    }
    public function redeemPoints(Request $request)
    {
        $userId = $request->input('user_id');
        $redeemId = $request->input('redeem_id');
        if (!filter_var($userId, FILTER_VALIDATE_INT) || (int) $userId < 1) {
            return response()->json(['status' => 'error', 'message' => 'user_id tidak valid.'], 422);
        }
        if (!filter_var($redeemId, FILTER_VALIDATE_INT) || (int) $redeemId < 1) {
            return response()->json(['status' => 'error', 'message' => 'redeem_id tidak valid.'], 422);
        }
        $userId = (int) $userId;
        $redeemId = (int) $redeemId;
        try {
            $result = DB::transaction(function () use ($userId, $redeemId) {
                $user = DB::table('mst_users')->where('id', $userId)->lockForUpdate()->first();
                if (!$user) {
                    throw new \Exception('User tidak ditemukan.', 404);
                }
                $redeemOption = DB::table('mst_redeem_point')->where('id', $redeemId)->first();
                if (!$redeemOption) {
                    throw new \Exception('Opsi penukaran tidak ditemukan.', 404);
                }
                $pointsNeeded = (int) $redeemOption->amount_poin;
                if (($user->points ?? 0) < $pointsNeeded) {
                    throw new \Exception('Poin Anda tidak mencukupi.', 402);
                }
                DB::table('mst_users')->where('id', $userId)->decrement('points', $pointsNeeded);
                DB::table('mst_users')->where('id', $userId)->increment('koin', (int) $redeemOption->espect_koin);
                DB::table('trx_reedem_poin')->insert([
                    'user_id' => $userId,
                    'redeem_id' => $redeemId,
                    'amount' => $pointsNeeded,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                return ['user' => DB::table('mst_users')->where('id', $userId)->first()];
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Poin berhasil ditukar!',
                'results' => $this->formatUser($result['user'])
            ], 200);
        } catch (\Exception $e) {
            $errorCode = $e->getCode();
            if ($errorCode === 404 || $errorCode === 402) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], $errorCode);
            }
            return response()->json(['status' => 'error', 'message' => 'Gagal menukar poin.', 'error_detail' => $e->getMessage()], 500);
        }
    }
    public function deleteAccount(Request $request)
    {
        $userId = $request->input('user_id');
        if (!filter_var($userId, FILTER_VALIDATE_INT) || (int) $userId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'user_id tidak valid.'
            ], 422);
        }
        $userId = (int) $userId;
        try {
            $user = DB::table('mst_users')->where('id', $userId)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan.'
                ], 404);
            }
            if ((int) ($user->deleted ?? 0) === 1) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Akun sudah dihapus.'
                ], 200);
            }
            DB::table('mst_users')
                ->where('id', $userId)
                ->update([
                    'deleted' => 1,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Akun berhasil dihapus.'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus akun.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function register(Request $request)
    {
        $name = trim((string) $request->input('name', ''));
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');
        if ($name === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'name wajib diisi.'
            ], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => 'error',
                'message' => 'email tidak valid.'
            ], 422);
        }
        if (strlen($password) < 6) {
            return response()->json([
                'status' => 'error',
                'message' => 'password minimal 6 karakter.'
            ], 422);
        }
        try {
            $emailExists = DB::table('mst_users')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->exists();
            if ($emailExists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email sudah terdaftar.'
                ], 409);
            }
            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();
            $userId = DB::table('mst_users')->insertGetId([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'api_token' => $token,
                'auth_provider' => 'local',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $user = DB::table('mst_users')->where('id', $userId)->first();
            return response()->json([
                'status' => 'success',
                'message' => 'Register berhasil.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], 201);
        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), 'mst_users_email_unique') !== false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email sudah terdaftar.'
                ], 409);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal register.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function login(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'email dan password wajib diisi.'
            ], 422);
        }
        try {
            $user = DB::table('mst_users')
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();
            if (!$user || $user->password === null || $user->password === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengguna tidak ditemukan.'
                ], 401);
            }
            if ((int) ($user->deleted ?? 0) === 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun sudah dihapus.'
                ], 403);
            }
            $passwordInfo = password_get_info((string) $user->password);
            $isHashedPassword = $passwordInfo['algo'] !== 0;
            $passwordValid = $isHashedPassword
                ? password_verify($password, (string) $user->password)
                : hash_equals((string) $user->password, $password);
            if (!$passwordValid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengguna tidak ditemukan.'
                ], 401);
            }
            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();
            $updateData = [
                'api_token' => $token,
                'last_login_at' => $now,
                'updated_at' => $now,
            ];
            if (!$isHashedPassword) {
                $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
            }
            DB::table('mst_users')->where('id', $user->id)->update($updateData);
            $user = DB::table('mst_users')->where('id', $user->id)->first();
            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal login.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function googleLogin(Request $request)
    {
        $idToken = (string) $request->input('id_token', '');
        if ($idToken === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'id_token wajib diisi.'
            ], 422);
        }
        $payload = $this->verifyGoogleIdToken($idToken);
        if ($payload === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token Google tidak valid atau kedaluwarsa.'
            ], 401);
        }
        $googleId = (string) ($payload['sub'] ?? '');
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $name = trim((string) ($payload['name'] ?? ''));
        $avatar = (string) ($payload['picture'] ?? '');
        $emailVerified = ($payload['email_verified'] ?? 'false') === true
            || ($payload['email_verified'] ?? 'false') === 'true';
        if ($googleId === '' || $email === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Data akun Google tidak lengkap.'
            ], 422);
        }
        try {
            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();
            // 1. Cari berdasarkan google_id, lalu fallback ke email.
            $user = DB::table('mst_users')->where('google_id', $googleId)->first();
            if (!$user) {
                $user = DB::table('mst_users')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();
            }
            if ($user) {
                if ((int) ($user->deleted ?? 0) === 1) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 'ACCOUNT_DELETED',
                        'message' => 'Akun sudah dihapus.'
                    ], 403);
                }
                // 2. User sudah ada -> tautkan google_id & perbarui sesi.
                $updateData = [
                    'google_id' => $googleId,
                    'api_token' => $token,
                    'last_login_at' => $now,
                    'updated_at' => $now,
                ];
                if ($avatar !== '') {
                    $updateData['google_avatar'] = $avatar;
                }
                if (($user->name ?? '') === '' && $name !== '') {
                    $updateData['name'] = $name;
                }
                if ($emailVerified && empty($user->email_verified_at)) {
                    $updateData['email_verified_at'] = $now;
                }
                DB::table('mst_users')->where('id', $user->id)->update($updateData);
                $user = DB::table('mst_users')->where('id', $user->id)->first();
                $statusCode = 200;
            } else {
                // 3. User baru -> buat akun via Google (tanpa password).
                $userId = DB::table('mst_users')->insertGetId([
                    'name' => $name !== '' ? $name : strstr($email, '@', true),
                    'email' => $email,
                    'password' => null,
                    'google_id' => $googleId,
                    'google_avatar' => $avatar !== '' ? $avatar : null,
                    'auth_provider' => 'google',
                    'email_verified_at' => $emailVerified ? $now : null,
                    'api_token' => $token,
                    'last_login_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $user = DB::table('mst_users')->where('id', $userId)->first();
                $statusCode = 201;
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Login Google berhasil.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], $statusCode);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal login Google.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function reactivateGoogleAccount(Request $request)
    {
        $idToken = (string) $request->input('id_token', '');
        if ($idToken === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'id_token wajib diisi.'
            ], 422);
        }
        $payload = $this->verifyGoogleIdToken($idToken);
        if ($payload === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token Google tidak valid atau kedaluwarsa.'
            ], 401);
        }
        $googleId = (string) ($payload['sub'] ?? '');
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $name = trim((string) ($payload['name'] ?? ''));
        $avatar = (string) ($payload['picture'] ?? '');
        $emailVerified = ($payload['email_verified'] ?? 'false') === true
            || ($payload['email_verified'] ?? 'false') === 'true';
        if ($googleId === '' || $email === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Data akun Google tidak lengkap.'
            ], 422);
        }
        try {
            $user = DB::table('mst_users')->where('google_id', $googleId)->first();
            if (!$user) {
                $user = DB::table('mst_users')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();
            }
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan.'
                ], 404);
            }
            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();
            $updateData = [
                'deleted' => 0,
                'google_id' => $googleId,
                'api_token' => $token,
                'last_login_at' => $now,
                'updated_at' => $now,
            ];
            if ($avatar !== '') {
                $updateData['google_avatar'] = $avatar;
            }
            if (($user->name ?? '') === '' && $name !== '') {
                $updateData['name'] = $name;
            }
            if ($emailVerified && empty($user->email_verified_at)) {
                $updateData['email_verified_at'] = $now;
            }
            DB::table('mst_users')->where('id', $user->id)->update($updateData);
            $user = DB::table('mst_users')->where('id', $user->id)->first();
            return response()->json([
                'status' => 'success',
                'message' => 'Akun berhasil diaktifkan kembali.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengaktifkan akun.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function appleLogin(Request $request)
    {
        $idToken = (string) $request->input('id_token', '');
        if ($idToken === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'id_token wajib diisi.'
            ], 422);
        }

        $payload = $this->verifyAppleIdToken($idToken);
        if ($payload === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token Apple tidak valid atau kedaluwarsa.'
            ], 401);
        }

        $appleId = (string) ($payload['sub'] ?? '');
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $name = trim((string) $request->input('full_name', ''));
        $emailVerified = ($payload['email_verified'] ?? 'false') === true
            || ($payload['email_verified'] ?? 'false') === 'true';

        if ($appleId === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Data akun Apple tidak lengkap.'
            ], 422);
        }

        try {
            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();
            $appleIdColumn = $this->getAppleProviderIdColumn();
            $user = DB::table('mst_users')->where($appleIdColumn, $appleId)->first();

            if (!$user && $email !== '') {
                $user = DB::table('mst_users')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();
            }

            if ($user) {
                if ((int) ($user->deleted ?? 0) === 1) {
                    return response()->json([
                        'status' => 'error',
                        'code' => 'ACCOUNT_DELETED',
                        'message' => 'Akun sudah dihapus.'
                    ], 403);
                }

                $updateData = [
                    $appleIdColumn => $appleId,
                    'auth_provider' => 'apple',
                    'api_token' => $token,
                    'last_login_at' => $now,
                    'updated_at' => $now,
                ];
                if (($user->name ?? '') === '' && $name !== '') {
                    $updateData['name'] = $name;
                }
                if ($emailVerified && empty($user->email_verified_at)) {
                    $updateData['email_verified_at'] = $now;
                }
                DB::table('mst_users')->where('id', $user->id)->update($updateData);
                $user = DB::table('mst_users')->where('id', $user->id)->first();
                $statusCode = 200;
            } else {
                if ($email === '') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Email Apple tidak tersedia. Hapus akses Bacaan dari pengaturan Apple ID lalu coba login kembali.'
                    ], 422);
                }

                $userId = DB::table('mst_users')->insertGetId([
                    'name' => $name !== '' ? $name : strstr($email, '@', true),
                    'email' => $email,
                    'password' => null,
                    $appleIdColumn => $appleId,
                    'auth_provider' => 'apple',
                    'email_verified_at' => $emailVerified ? $now : null,
                    'api_token' => $token,
                    'last_login_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $user = DB::table('mst_users')->where('id', $userId)->first();
                $statusCode = 201;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Login Apple berhasil.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], $statusCode);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal login Apple.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function reactivateAppleAccount(Request $request)
    {
        $idToken = (string) $request->input('id_token', '');
        if ($idToken === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'id_token wajib diisi.'
            ], 422);
        }

        $payload = $this->verifyAppleIdToken($idToken);
        if ($payload === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token Apple tidak valid atau kedaluwarsa.'
            ], 401);
        }

        $appleId = (string) ($payload['sub'] ?? '');
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $name = trim((string) $request->input('full_name', ''));
        $emailVerified = ($payload['email_verified'] ?? 'false') === true
            || ($payload['email_verified'] ?? 'false') === 'true';

        if ($appleId === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Data akun Apple tidak lengkap.'
            ], 422);
        }

        try {
            $appleIdColumn = $this->getAppleProviderIdColumn();
            $user = DB::table('mst_users')->where($appleIdColumn, $appleId)->first();

            if (!$user && $email !== '') {
                $user = DB::table('mst_users')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->first();
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan.'
                ], 404);
            }

            $now = date('Y-m-d H:i:s');
            $token = $this->generateApiToken();
            $updateData = [
                'deleted' => 0,
                $appleIdColumn => $appleId,
                'auth_provider' => 'apple',
                'api_token' => $token,
                'last_login_at' => $now,
                'updated_at' => $now,
            ];
            if (($user->name ?? '') === '' && $name !== '') {
                $updateData['name'] = $name;
            }
            if ($emailVerified && empty($user->email_verified_at)) {
                $updateData['email_verified_at'] = $now;
            }
            DB::table('mst_users')->where('id', $user->id)->update($updateData);
            $user = DB::table('mst_users')->where('id', $user->id)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Akun berhasil diaktifkan kembali.',
                'token' => $token,
                'results' => $this->formatUser($user),
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengaktifkan akun.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    // Verifikasi id_token Google lewat endpoint tokeninfo (memvalidasi tanda
    // tangan & masa berlaku di sisi Google). Mengembalikan payload klaim jika
    // valid & "aud" cocok dengan client ID kita, atau null jika tidak valid.
    private function verifyGoogleIdToken(string $idToken): ?array
    {
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false || $httpCode !== 200) {
            return null;
        }
        $payload = json_decode($response, true);
        if (!is_array($payload)) {
            return null;
        }
        // Pastikan token diterbitkan oleh Google.
        $iss = $payload['iss'] ?? '';
        if ($iss !== 'https://accounts.google.com' && $iss !== 'accounts.google.com') {
            return null;
        }
        // Pastikan token ditujukan untuk aplikasi kita (cegah token disusupkan).
        $aud = $payload['aud'] ?? '';
        if (!in_array($aud, $this->getGoogleAllowedClientIds(), true)) {
            return null;
        }
        // Pastikan belum kedaluwarsa.
        if (!isset($payload['exp']) || (int) $payload['exp'] < time()) {
            return null;
        }
        return $payload;
    }
    private function getGoogleAllowedClientIds(): array
    {
        static $allowedClientIds = null;

        if ($allowedClientIds !== null) {
            return $allowedClientIds;
        }

        $allowedClientIds = self::GOOGLE_FALLBACK_ALLOWED_CLIENT_IDS;
        $googleServices = $this->loadGoogleServicesJson();

        if ($googleServices !== null) {
            foreach (($googleServices['client'] ?? []) as $client) {
                if (!is_array($client)) {
                    continue;
                }

                $packageName = $client['client_info']['android_client_info']['package_name'] ?? null;
                if ($packageName !== self::GOOGLE_ANDROID_PACKAGE_NAME) {
                    continue;
                }

                foreach (($client['oauth_client'] ?? []) as $oauthClient) {
                    if (!is_array($oauthClient)) {
                        continue;
                    }

                    $clientId = $oauthClient['client_id'] ?? '';
                    if (is_string($clientId) && $clientId !== '') {
                        $allowedClientIds[] = $clientId;
                    }
                }

                foreach (($client['services']['appinvite_service']['other_platform_oauth_client'] ?? []) as $oauthClient) {
                    if (!is_array($oauthClient)) {
                        continue;
                    }

                    $clientId = $oauthClient['client_id'] ?? '';
                    if (is_string($clientId) && $clientId !== '') {
                        $allowedClientIds[] = $clientId;
                    }
                }
            }
        }

        return $allowedClientIds = array_values(array_unique($allowedClientIds));
    }

    private function verifyAppleIdToken(string $idToken): ?array
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            return null;
        }

        $headerJson = $this->base64UrlDecode($parts[0]);
        $payloadJson = $this->base64UrlDecode($parts[1]);
        if ($headerJson === null || $payloadJson === null) {
            return null;
        }

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);
        if (!is_array($header) || !is_array($payload)) {
            return null;
        }

        if (($header['alg'] ?? '') !== 'RS256') {
            return null;
        }

        if (!$this->verifyAppleJwtSignature($parts[0] . '.' . $parts[1], $parts[2], $header)) {
            return null;
        }

        if (($payload['iss'] ?? '') !== 'https://appleid.apple.com') {
            return null;
        }

        $audience = $payload['aud'] ?? '';
        $allowedAudiences = [self::APPLE_BUNDLE_ID];
        if (is_array($audience)) {
            if (count(array_intersect($audience, $allowedAudiences)) === 0) {
                return null;
            }
        } elseif (!in_array($audience, $allowedAudiences, true)) {
            return null;
        }

        if (!isset($payload['exp']) || (int) $payload['exp'] < time()) {
            return null;
        }

        if (($payload['sub'] ?? '') === '') {
            return null;
        }

        return $payload;
    }

    private function verifyAppleJwtSignature(string $signingInput, string $signaturePart, array $header): bool
    {
        $kid = (string) ($header['kid'] ?? '');
        if ($kid === '') {
            return false;
        }

        $signature = $this->base64UrlDecode($signaturePart);
        if ($signature === null) {
            return false;
        }

        $keys = $this->getApplePublicKeys();
        if (!is_array($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if (!is_array($key) || ($key['kid'] ?? '') !== $kid || ($key['kty'] ?? '') !== 'RSA') {
                continue;
            }

            $pem = $this->jwkToPem((string) ($key['n'] ?? ''), (string) ($key['e'] ?? ''));
            if ($pem === null) {
                continue;
            }

            $publicKey = openssl_pkey_get_public($pem);
            if ($publicKey === false) {
                continue;
            }

            return openssl_verify($signingInput, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
        }

        return false;
    }

    private function getApplePublicKeys(): ?array
    {
        static $keys = null;
        if ($keys !== null) {
            return $keys;
        }

        $ch = curl_init('https://appleid.apple.com/auth/keys');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $payload = json_decode($response, true);
        if (!is_array($payload) || !is_array($payload['keys'] ?? null)) {
            return null;
        }

        return $keys = $payload['keys'];
    }

    private function jwkToPem(string $modulus, string $exponent): ?string
    {
        $n = $this->base64UrlDecode($modulus);
        $e = $this->base64UrlDecode($exponent);
        if ($n === null || $e === null) {
            return null;
        }

        $rsaPublicKey = $this->asn1Sequence(
            $this->asn1Integer($n) . $this->asn1Integer($e)
        );
        $algorithmIdentifier = hex2bin('300d06092a864886f70d0101010500');
        if ($algorithmIdentifier === false) {
            return null;
        }

        $subjectPublicKeyInfo = $this->asn1Sequence(
            $algorithmIdentifier . "\x03" . $this->asn1Length(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey
        );

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    private function asn1Integer(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '') {
            $bytes = "\x00";
        }
        if (ord($bytes[0]) > 0x7f) {
            $bytes = "\x00" . $bytes;
        }

        return "\x02" . $this->asn1Length(strlen($bytes)) . $bytes;
    }

    private function asn1Sequence(string $bytes): string
    {
        return "\x30" . $this->asn1Length(strlen($bytes)) . $bytes;
    }

    private function asn1Length(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }

        $bytes = '';
        while ($length > 0) {
            $bytes = chr($length & 0xff) . $bytes;
            $length >>= 8;
        }

        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    private function base64UrlDecode(string $value): ?string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if ($decoded !== false) {
            return $decoded;
        }

        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return $decoded === false ? null : $decoded;
    }

    private function getAppleProviderIdColumn(): string
    {
        static $column = null;
        if ($column !== null) {
            return $column;
        }

        try {
            if (DB::getSchemaBuilder()->hasColumn('mst_users', 'apple_id')) {
                return $column = 'apple_id';
            }
        } catch (\Throwable $e) {
            // Server lama belum tentu punya schema builder aktif di context ini.
        }

        return $column = 'google_id';
    }

    private function loadGoogleServicesJson(): ?array
    {
        foreach ($this->getGoogleServicesJsonPaths() as $path) {
            if (!is_string($path) || $path === '' || !is_readable($path)) {
                continue;
            }

            $contents = file_get_contents($path);
            if ($contents === false) {
                continue;
            }

            $config = json_decode($contents, true);
            if (is_array($config)) {
                return $config;
            }
        }

        return null;
    }
    private function getGoogleServicesJsonPaths(): array
    {
        $paths = [];
        $envPath = function_exists('env')
            ? env('GOOGLE_SERVICES_JSON_PATH', getenv('GOOGLE_SERVICES_JSON_PATH') ?: '')
            : getenv('GOOGLE_SERVICES_JSON_PATH');
        if (is_string($envPath) && $envPath !== '') {
            $paths[] = $envPath;
        }
        if (function_exists('base_path')) {
            $paths[] = base_path('storage/app/google-services.json');
            $paths[] = base_path('storage/app/firebase/google-services.json');
            $paths[] = base_path('google-services.json');
            $paths[] = base_path('android/app/google-services.json');
        }
        if (function_exists('storage_path')) {
            $paths[] = storage_path('app/google-services.json');
            $paths[] = storage_path('app/firebase/google-services.json');
        }

        $paths[] = __DIR__ . '/google-services.json';
        $paths[] = dirname(__DIR__) . '/google-services.json';
        $paths[] = dirname(__DIR__, 2) . '/google-services.json';
        $paths[] = dirname(__DIR__, 3) . '/google-services.json';
        $paths[] = dirname(__DIR__, 3) . '/android/app/google-services.json';

        return array_values(array_unique($paths));
    }
    public function markNotifikasiRead(Request $request)
    {
        $userId = $request->input('user_id');
        $notifikasiId = $request->input('notifikasi_id', $request->input('notification_id'));
        if (!filter_var($userId, FILTER_VALIDATE_INT) || (int) $userId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'user_id wajib diisi dan harus berupa angka lebih dari 0.'
            ], 422);
        }
        if (!filter_var($notifikasiId, FILTER_VALIDATE_INT) || (int) $notifikasiId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'notifikasi_id wajib diisi dan harus berupa angka lebih dari 0.'
            ], 422);
        }
        $userId = (int) $userId;
        $notifikasiId = (int) $notifikasiId;
        try {
            if (!DB::table('mst_users')->where('id', $userId)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan.'
                ], 404);
            }
            if (!DB::table('mst_notifikasi')->where('id', $notifikasiId)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data notifikasi tidak ditemukan.'
                ], 404);
            }
            $now = date('Y-m-d H:i:s');
            $rows = DB::select(
                "INSERT INTO mst_notifikasi_read (user_id, notifikasi_id, read_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?)
                 ON CONFLICT (user_id, notifikasi_id) DO UPDATE
                 SET read_at = EXCLUDED.read_at,
                     updated_at = EXCLUDED.updated_at
                 RETURNING id, user_id, notifikasi_id, read_at, created_at, updated_at",
                [$userId, $notifikasiId, $now, $now, $now]
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Notifikasi berhasil ditandai sudah dibaca.',
                'results' => $rows[0],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan status read notifikasi.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function claimDailyReward(Request $request)
    {
        $userId = $request->input('user_id');
        $rewardId = $request->input('daily_reward_id');
        $coinReward = $request->input('coin_reward');
        $scheduleId = $request->input('daily_reward_schedule_id');
        // Validation
        if (!filter_var($userId, FILTER_VALIDATE_INT) || (int) $userId < 1) {
            return response()->json(['status' => 'error', 'message' => 'user_id tidak valid.'], 422);
        }
        if (!filter_var($rewardId, FILTER_VALIDATE_INT) || (int) $rewardId < 1) {
            return response()->json(['status' => 'error', 'message' => 'daily_reward_id tidak valid.'], 422);
        }
        if (!filter_var($coinReward, FILTER_VALIDATE_INT) || (int) $coinReward < 0) {
            return response()->json(['status' => 'error', 'message' => 'coin_reward tidak valid.'], 422);
        }
        if ($scheduleId !== null && (!filter_var($scheduleId, FILTER_VALIDATE_INT) || (int) $scheduleId < 1)) {
            return response()->json(['status' => 'error', 'message' => 'daily_reward_schedule_id tidak valid.'], 422);
        }
        $userId = (int) $userId;
        $rewardId = (int) $rewardId;
        $scheduleId = $scheduleId !== null ? (int) $scheduleId : null;
        $today = date('Y-m-d');
        $todayDayOfWeek = (int) date('N');
        $now = date('Y-m-d H:i:s');
        try {
            $result = DB::transaction(function () use ($userId, $rewardId, $scheduleId, $today, $todayDayOfWeek, $now) {
                $claimTableHasRewardVideoId = $this->tableHasColumn('mst_daily_reward_claims', 'reward_video_id');
                $claimTableHasClaimKey = $this->tableHasColumn('mst_daily_reward_claims', 'claim_key');
                $rewardVideoId = null;
                $claimKey = 'daily';
                // Lock user row to prevent race conditions on points update
                $user = DB::table('mst_users')->where('id', $userId)->lockForUpdate()->first();
                if (!$user) {
                    throw new \Exception('User tidak ditemukan.', 404);
                }
                $reward = DB::table('mst_daily_rewards')
                    ->where('id', $rewardId)
                    ->where('status', true)
                    ->first();
                if (!$reward) {
                    throw new \Exception('Reward tidak ditemukan atau tidak aktif.', 404);
                }
                if ((int) $reward->reward_type_id === 2) {
                    $scheduleTable = $this->getDailyRewardScheduleTable();
                    if (!$scheduleTable) {
                        throw new \Exception('Table schedule reward video tidak ditemukan.', 500);
                    }
                    $scheduleHasRewardVideoId = $this->tableHasColumn($scheduleTable, 'reward_video_id');

                    $scheduleQuery = DB::table($scheduleTable)
                        ->where('daily_reward_id', $rewardId)
                        ->where('day_of_week', $todayDayOfWeek);

                    if ($scheduleId !== null) {
                        $scheduleQuery->where('id', $scheduleId);
                    }

                    $schedules = $scheduleQuery->orderBy('id', 'asc')->get();
                    if ($schedules->isEmpty()) {
                        $message = $scheduleId !== null
                            ? "Schedule reward video tidak cocok. daily_reward_id={$rewardId}, schedule_id={$scheduleId}."
                            : "Reward video tidak tersedia hari ini. daily_reward_id={$rewardId}, day_of_week={$todayDayOfWeek}.";
                        throw new \Exception($message, 404);
                    }

                    $schedule = null;
                    foreach ($schedules as $candidateSchedule) {
                        $candidateRewardVideoId = $scheduleHasRewardVideoId
                            ? (int) ($candidateSchedule->reward_video_id ?? 0)
                            : null;

                        if ($scheduleHasRewardVideoId && $candidateRewardVideoId < 1) {
                            continue;
                        }

                        if (!$this->dailyRewardClaimExists($userId, $rewardId, $today, $candidateRewardVideoId, $claimTableHasRewardVideoId, $claimTableHasClaimKey)) {
                            $schedule = $candidateSchedule;
                            $rewardVideoId = $candidateRewardVideoId;
                            break;
                        }
                    }

                    if (!$schedule) {
                        throw new \Exception('Semua video iklan hari ini sudah diklaim.', 409);
                    }

                    if ($scheduleHasRewardVideoId) {
                        $videoTable = $this->getDailyRewardVideoTable();
                        if ($videoTable && !DB::table($videoTable)->where('id', $rewardVideoId)->exists()) {
                            throw new \Exception("Video reward tidak ditemukan. reward_video_id={$rewardVideoId}.", 404);
                        }

                        $claimKey = $this->dailyRewardVideoClaimKey($rewardVideoId);
                    }
                }
                $coinReward = (int) $reward->coin_reward;
                if ((int) $reward->reward_type_id !== 2 && $this->dailyRewardClaimExists($userId, $rewardId, $today, null, $claimTableHasRewardVideoId, $claimTableHasClaimKey)) {
                    throw new \Exception('Reward sudah diklaim hari ini.', 409);
                }

                // Insert claim record. The unique constraint will handle race conditions for claims.
                $claimPayload = [
                    'user_id' => $userId,
                    'daily_reward_id' => $rewardId,
                    'claim_date' => $today,
                    'coin_reward' => $coinReward,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($claimTableHasRewardVideoId) {
                    $claimPayload['reward_video_id'] = $rewardVideoId;
                }
                if ($claimTableHasClaimKey) {
                    $claimPayload['claim_key'] = $claimKey;
                }

                DB::table('mst_daily_reward_claims')->insert($claimPayload);
                // Update user's points. Assuming the column is 'points'.
                DB::table('mst_users')->where('id', $userId)->increment('points', $coinReward);
                // Ambil ulang nilai poin terbaru setelah increment untuk memastikan data yang dikembalikan akurat.
                // $user->points berisi nilai LAMA sebelum di-increment.
                $newPointsAfterIncrement = DB::table('mst_users')->where('id', $userId)->value('points');
                return ['new_points' => (int) $newPointsAfterIncrement];
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Poin berhasil diklaim!',
                'results' => [
                    'new_points' => $result['new_points']
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle unique constraint violation from DB
            if ($this->isDailyRewardClaimUniqueViolation($e)) {
                return response()->json(['status' => 'error', 'message' => 'Reward sudah diklaim hari ini.'], 409);
            }
            if ((int) $e->getCode() === 409) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
            }
            if ((int) $e->getCode() === 404) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengklaim reward.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getNotifikasiByUser($user_id)
    {
        if (!filter_var($user_id, FILTER_VALIDATE_INT) || (int) $user_id < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'user_id harus berupa angka lebih dari 0.'
            ], 422);
        }
        $userId = (int) $user_id;
        try {
            if (!DB::table('mst_users')->where('id', $userId)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data user tidak ditemukan.'
                ], 404);
            }
            $data = DB::select(
                "SELECT
                    n.*,
                    CASE WHEN r.id IS NULL THEN false ELSE true END AS is_read,
                    r.read_at
                 FROM mst_notifikasi n
                 LEFT JOIN mst_notifikasi_read r
                   ON r.notifikasi_id = n.id
                  AND r.user_id = ?
                 ORDER BY n.id DESC",
                [$userId]
            );
            return response()->json([
                'status' => 'success',
                'count' => count($data),
                'results' => $data,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data notifikasi user.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function getVersion()
    {
        try {
            $data = DB::table('mst_versions')->get();
            return response()->json([
                'status' => 'success',
                'count'  => count($data),
                'results' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data version.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function insertCeritaPanjang()
    {
       try {
        $dataCerita = [
            ['judul' => 'The Roses Of Night', 'kategori' => 4],
            ['judul' => 'Dear You', 'kategori' => 1],
            ['judul' => 'Senja', 'kategori' => 1],
            ['judul' => 'Kala Senja Menyapa', 'kategori' => 1],
            ['judul' => 'Sang Penerang', 'kategori' => 4],
            ['judul' => 'Di Taman Puisi', 'kategori' => 1],
            ['judul' => 'Buku Catatan', 'kategori' => 4],
            ['judul' => 'Deeper Love', 'kategori' => 1],
            ['judul' => 'Cinta Tanpa Jeda', 'kategori' => 1],
            ['judul' => 'Tujuh Kelana', 'kategori' => 2],
        ];
        $payload = [];
        foreach ($dataCerita as $item) {
            $isiCerita = [
                "chapter 1" => "Awal mula kisah " . $item['judul'] . " dimulai di sini. Narasi panjang mengalir menceritakan pengenalan karakter utama dan latar belakang dunia yang sedang dibangun untuk memikat pembaca sejak baris pertama.",
                "chapter 2" => "Konflik mulai muncul di bab kedua ini. Ketegangan meningkat saat rahasia mulai terungkap dan tantangan besar menghadang langkah sang tokoh utama dalam perjalanan " . $item['judul'] . ".",
                "chapter 3" => "Puncak emosional terjadi di bab ketiga. Semua taruhan dikerahkan, dan karakter harus memilih antara idealisme atau kenyataan pahit yang harus mereka hadapi demi mencapai tujuan akhir.",
                "chapter 4" => "Resolusi dan konklusi sementara. Bab ini menutup bagian pertama dari " . $item['judul'] . " dengan sebuah 'cliffhanger' atau penyelesaian yang manis, memberikan kepuasan bagi para pembaca setia."
            ];
            $payload[] = [
                'judul'         => $item['judul'],
                'parts'         => 4,
                'isi_cerita'    => json_encode($isiCerita),
                'status'        => true,
                'total_read'    => rand(100, 5000),
                'total_vote'    => rand(10, 1000),
                'total_share'   => rand(5, 500),
                'recomendation' => (bool)rand(0, 1),
                'wajib_dibaca'  => (bool)rand(0, 1),
                'id_kategori'   => $item['kategori'],
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
        }
        DB::table('mst_cerita')->insert($payload);
        return response()->json([
            'status' => 'success',
            'message' => '10 Judul cerita berhasil ditambahkan ke database novel_mora'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }}
    public function trxRead(Request $request)
    {
        return $this->incrementNovelCounter($request, 'read');
    }
    public function trxVote(Request $request)
    {
        return $this->incrementNovelCounter($request, 'vote');
    }
    public function trxShare(Request $request)
    {
        return $this->incrementNovelCounter($request, 'share');
    }
    public function verifyPurchase(Request $request)
    {
        $userId = $request->input('user_id');
        $platform = strtolower(trim((string) $request->input('platform', '')));
        $productId = trim((string) $request->input('product_id', ''));
        $transactionId = trim((string) $request->input('transaction_id', ''));
        // Validasi input
        if (!filter_var($userId, FILTER_VALIDATE_INT) || (int) $userId < 1) {
            return response()->json(['status' => 'error', 'message' => 'user_id tidak valid.'], 422);
        }
        if (!in_array($platform, ['ios', 'android'], true)) {
            return response()->json(['status' => 'error', 'message' => 'platform wajib ios atau android.'], 422);
        }
        if (empty($productId)) {
            return response()->json(['status' => 'error', 'message' => 'product_id wajib diisi.'], 422);
        }
        if (empty($transactionId)) {
            return response()->json(['status' => 'error', 'message' => 'transaction_id wajib diisi.'], 422);
        }
        try {
            $result = DB::transaction(function () use ($userId, $productId, $transactionId) {
                $now = date('Y-m-d H:i:s');
                $userId = (int) $userId;

                $existingKoinTrx = DB::table('trx_buy_koin')->where('transaction_id', $transactionId)->first();
                $existingPaketTrx = DB::table('trx_buy_paket')->where('transaction_id', $transactionId)->first();
                if ($existingKoinTrx || $existingPaketTrx) {
                    throw new \Exception('Transaksi ini sudah pernah diproses.', 409);
                }

                $user = DB::table('mst_users')->where('id', $userId)->lockForUpdate()->first();
                if (!$user) {
                    throw new \Exception('User tidak ditemukan.', 404);
                }

                $coinProduct = DB::table('mst_subs_koin')
                    ->where('product_id', $productId)
                    ->where('tipe', 'koin')
                    ->where('status', true)
                    ->first();

                if ($coinProduct) {
                    $koinToAdd = (int) ($coinProduct->jumlah_koin ?? 0);
                    $price = (int) ($coinProduct->harga ?? 0);
                    if ($koinToAdd < 1) {
                        throw new \Exception('Jumlah koin produk tidak valid.', 422);
                    }

                    DB::table('trx_buy_koin')->insert([
                        'user_id' => $userId,
                        'amount_koin' => $koinToAdd,
                        'amount_price' => $price,
                        'status_payment' => 'verified',
                        'transaction_id' => $transactionId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('mst_users')->where('id', $userId)->increment('koin', $koinToAdd);

                    return [
                        'type' => 'koin',
                        'user' => DB::table('mst_users')->where('id', $userId)->first(),
                    ];
                }

                $packageProduct = DB::table('mst_subs')
                    ->where('product_id', $productId)
                    ->where('tipe', 'langganan')
                    ->where('status', true)
                    ->first();

                if (!$packageProduct) {
                    throw new \Exception('Produk pembelian tidak ditemukan.', 404);
                }

                $packageName = (string) ($packageProduct->nama_paket ?? $productId);
                $countDaily = $this->getPackageCountDaily($packageName);
                $price = (string) ($packageProduct->harga ?? '0');
                $activePackage = $this->getActiveSubscription($userId);
                $baseTimestamp = time();
                if ($activePackage && strtotime((string) $activePackage->end_date) > $baseTimestamp) {
                    $baseTimestamp = strtotime((string) $activePackage->end_date);
                }
                $startDate = $now;
                $endDate = date('Y-m-d H:i:s', strtotime("+{$countDaily} days", $baseTimestamp));

                DB::table('trx_buy_paket')->insert([
                    'user_id' => $userId,
                    'transaction_id' => $transactionId,
                    'amount_price' => $price,
                    'nama_paket' => $packageName,
                    'status_payment' => 'verified',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'count_daily' => (string) $countDaily,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'status' => true,
                ]);

                return [
                    'type' => 'paket',
                    'user' => DB::table('mst_users')->where('id', $userId)->first(),
                ];
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Pembelian terverifikasi.',
                'valid' => true,
                'purchase_type' => $result['type'],
                'results' => $this->formatUser($result['user']),
            ], 200);
        } catch (\Exception $e) {
            $errorCode = $e->getCode();
            if ($errorCode === 409 || $errorCode === 404 || $errorCode === 422) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], $errorCode);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memverifikasi pembelian.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
    public function unlockChapter(Request $request)
    {
        $userId = $request->input('user_id');
        $novelId = $request->input('novel_id');
        $chapterKey = $request->input('chapter_key');
        $accessSource = trim((string) $request->input('access_source', 'coins'));
        $cost = 10; // Hardcoded cost
        // Validation
        if (!filter_var($userId, FILTER_VALIDATE_INT) || (int) $userId < 1) {
            return response()->json(['status' => 'error', 'message' => 'user_id tidak valid.'], 422);
        }
        if (!filter_var($novelId, FILTER_VALIDATE_INT) || (int) $novelId < 1) {
            return response()->json(['status' => 'error', 'message' => 'novel_id tidak valid.'], 422);
        }
        if (empty($chapterKey) || !preg_match('/^chapter \d+$/', $chapterKey)) {
            return response()->json(['status' => 'error', 'message' => 'chapter_key tidak valid.'], 422);
        }
        try {
            // Ekstrak ID chapter dari key "chapter 1" -> 1
            $chapterId = (int) str_replace('chapter ', '', $chapterKey);
            $result = DB::transaction(function () use ($userId, $novelId, $cost, $chapterId, $accessSource) {
                $now = date('Y-m-d H:i:s');
                $userId = (int) $userId;
                $novelId = (int) $novelId;
                $user = DB::table('mst_users')->where('id', $userId)->lockForUpdate()->first();
                if (!$user) {
                    throw new \Exception('User tidak ditemukan.', 404);
                }
                // Cek apakah chapter sudah pernah dibuka oleh user ini
                $isAlreadyUnlocked = DB::table('trx_unlock_chapter')
                    ->where('user_id', $userId)
                    ->where('cerita_id', $novelId)
                    ->where('chapter_id', $chapterId)
                    ->exists();
                if ($isAlreadyUnlocked) {
                    // Jika sudah, tidak perlu kurangi koin, langsung kembalikan data user
                    return ['user' => $user];
                }
                $activeSubscription = $this->getActiveSubscription($userId);
                if ($activeSubscription) {
                    DB::table('trx_unlock_chapter')->insert([
                        'user_id' => $userId,
                        'cerita_id' => $novelId,
                        'chapter_id' => $chapterId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    return ['user' => $user];
                }
                if ($accessSource === 'subscription') {
                    throw new \Exception('Langganan tidak aktif.', 402);
                }
                if (($user->koin ?? 0) < $cost) {
                    throw new \Exception('Koin tidak cukup.', 402);
                }
                // 1. Kurangi koin user
                DB::table('mst_users')->where('id', $userId)->decrement('koin', $cost);
                // 2. Catat transaksi unlock chapter
                DB::table('trx_unlock_chapter')->insert([
                    'user_id' => $userId,
                    'cerita_id' => $novelId,
                    'chapter_id' => $chapterId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $updatedUser = DB::table('mst_users')->where('id', $userId)->first();
                return ['user' => $updatedUser];
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Chapter berhasil dibuka!',
                'results' => $this->formatUser($result['user'])
            ], 200);
        } catch (\Exception $e) {
            $errorCode = $e->getCode();
            if ($errorCode === 404 || $errorCode === 402) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], $errorCode);
            }
            return response()->json(['status' => 'error', 'message' => 'Gagal membuka chapter.', 'error_detail' => $e->getMessage()], 500);
        }
    }
    private function formatUser($user): array
    {
        $data = (array) $user;
        unset($data['password']);
        // Pastikan points adalah integer
        if (isset($data['points'])) {
            $data['points'] = (int) $data['points'];
        } else {
            $data['points'] = 0;
        }
        // Pastikan koin adalah integer
        if (isset($data['koin'])) {
            $data['koin'] = (int) $data['koin'];
        } else {
            $data['koin'] = 0;
        }
        $activeSubscription = isset($data['id'])
            ? $this->getActiveSubscription((int) $data['id'])
            : null;
        $data['subscription_active'] = $activeSubscription !== null;
        $data['subscription'] = $activeSubscription ? [
            'id' => (int) $activeSubscription->id,
            'nama_paket' => (string) $activeSubscription->nama_paket,
            'start_date' => $activeSubscription->start_date,
            'end_date' => $activeSubscription->end_date,
            'count_daily' => (int) $activeSubscription->count_daily,
            'status_payment' => (string) $activeSubscription->status_payment,
        ] : null;
        return $data;
    }

    private function buildStoryChapters($story): array
    {
        $storyId = (int) $story->id;
        $rawChapters = json_decode((string) ($story->isi_cerita ?? ''), true);
        if (!is_array($rawChapters)) {
            return [];
        }

        $adsPlacements = $this->getStoryAdsPlacements($storyId);
        $chapters = [];
        $index = 0;

        foreach ($rawChapters as $chapterKey => $chapterValue) {
            $index++;
            $chapterNumber = $this->extractChapterNumber($chapterKey, $index);
            $isStructuredChapter = is_array($chapterValue);
            $title = $isStructuredChapter && isset($chapterValue['title']) && trim((string) $chapterValue['title']) !== ''
                ? (string) $chapterValue['title']
                : 'Chapter ' . $chapterNumber;
            $content = is_string($chapterValue)
                ? $chapterValue
                : ($isStructuredChapter && isset($chapterValue['content']) ? (string) $chapterValue['content'] : '');

            $chapters[] = [
                'number' => $chapterNumber,
                'key' => is_string($chapterKey) ? $chapterKey : 'chapter ' . $chapterNumber,
                'title' => $title,
                'content' => $content,
                'ads' => $adsPlacements[$chapterNumber] ?? $this->emptyChapterAds(),
            ];
        }

        return $chapters;
    }

    private function getStoryAdsPlacements(int $storyId): array
    {
        $rows = DB::table('mst_cerita_ads as ca')
            ->join('mst_ads as a', 'ca.ad_id', '=', 'a.id')
            ->where('ca.cerita_id', $storyId)
            ->where('a.status', true)
            ->select([
                'ca.after_chapter',
                'ca.is_global',
                DB::raw("COALESCE(NULLIF(ca.placement_position::text, ''), 'after') as placement_position"),
                'a.id',
                'a.title',
                'a.media_type',
                'a.media_url',
                'a.target_url',
            ])
            ->orderBy('ca.after_chapter', 'asc')
            ->orderBy('a.id', 'asc')
            ->get();

        $placements = [];
        foreach ($rows as $row) {
            $chapterNumber = (int) ($row->after_chapter ?? 0);
            if ($chapterNumber < 1) {
                continue;
            }

            $position = strtolower(trim((string) ($row->placement_position ?? 'after')));
            if ($position !== 'before') {
                $position = 'after';
            }

            if (!isset($placements[$chapterNumber])) {
                $placements[$chapterNumber] = $this->emptyChapterAds();
            }

            $placements[$chapterNumber][$position][] = [
                'id' => (int) $row->id,
                'title' => (string) ($row->title ?? ''),
                'media_type' => (string) ($row->media_type ?? ''),
                'media_url' => $row->media_url ?? null,
                'target_url' => $row->target_url ?? null,
                'is_global' => $this->toBoolean($row->is_global ?? false),
            ];
        }

        return $placements;
    }

    private function extractChapterNumber($chapterKey, int $fallbackNumber): int
    {
        if (preg_match('/chapter\s+(\d+)/i', (string) $chapterKey, $matches)) {
            return (int) $matches[1];
        }

        return $fallbackNumber;
    }

    private function emptyChapterAds(): array
    {
        return [
            'before' => [],
            'after' => [],
        ];
    }

    private function toBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $normalized = strtolower(trim((string) $value));
        return in_array($normalized, ['1', 'true', 't', 'yes', 'y', 'on'], true);
    }

    private function dailyRewardVideoClaimKey(int $rewardVideoId): string
    {
        return 'video:' . $rewardVideoId;
    }

    private function dailyRewardClaimExists(
        int $userId,
        int $rewardId,
        string $claimDate,
        ?int $rewardVideoId,
        bool $claimTableHasRewardVideoId,
        bool $claimTableHasClaimKey
    ): bool {
        $query = DB::table('mst_daily_reward_claims')
            ->where('user_id', $userId)
            ->where('daily_reward_id', $rewardId)
            ->where('claim_date', $claimDate);

        if ($rewardVideoId !== null && $rewardVideoId > 0) {
            if ($claimTableHasClaimKey) {
                $query->where('claim_key', $this->dailyRewardVideoClaimKey($rewardVideoId));
            } elseif ($claimTableHasRewardVideoId) {
                $query->where('reward_video_id', $rewardVideoId);
            }
        }

        return $query->exists();
    }

    private function isDailyRewardClaimUniqueViolation(\Exception $e): bool
    {
        $message = $e->getMessage();

        return (string) $e->getCode() === '23505'
            || strpos($message, 'daily_reward_claim_unique') !== false
            || strpos($message, 'mst_daily_reward_claims_user_id_daily_reward_id_claim_date_uniq') !== false
            || strpos($message, 'mst_daily_reward_claims_user_id_daily_reward_id_claim_date_unique') !== false;
    }

    private function getDailyRewardScheduleTable(): ?string
    {
        static $resolvedTable = false;

        if ($resolvedTable !== false) {
            return $resolvedTable;
        }

        foreach (self::DAILY_REWARD_SCHEDULE_TABLE_CANDIDATES as $tableName) {
            $row = DB::selectOne(
                "SELECT table_schema, table_name
                 FROM information_schema.tables
                 WHERE table_name = ?
                   AND table_schema NOT IN ('pg_catalog', 'information_schema')
                 ORDER BY CASE WHEN table_schema = 'public' THEN 0 ELSE 1 END
                 LIMIT 1",
                [$tableName]
            );

            if ($row) {
                $resolvedTable = $row->table_schema === 'public'
                    ? $row->table_name
                    : "{$row->table_schema}.{$row->table_name}";
                return $resolvedTable;
            }
        }

        $resolvedTable = null;
        return null;
    }

    private function getDailyRewardVideoTable(): ?string
    {
        static $resolvedTable = false;

        if ($resolvedTable !== false) {
            return $resolvedTable;
        }

        foreach (self::DAILY_REWARD_VIDEO_TABLE_CANDIDATES as $tableName) {
            $row = DB::selectOne(
                "SELECT table_schema, table_name
                 FROM information_schema.tables
                 WHERE table_name = ?
                   AND table_schema NOT IN ('pg_catalog', 'information_schema')
                 ORDER BY CASE WHEN table_schema = 'public' THEN 0 ELSE 1 END
                 LIMIT 1",
                [$tableName]
            );

            if ($row) {
                $resolvedTable = $row->table_schema === 'public'
                    ? $row->table_name
                    : "{$row->table_schema}.{$row->table_name}";
                return $resolvedTable;
            }
        }

        $resolvedTable = null;
        return null;
    }

    private function tableHasColumn(string $resolvedTable, string $columnName): bool
    {
        static $columnCache = [];
        $cacheKey = "{$resolvedTable}.{$columnName}";
        if (array_key_exists($cacheKey, $columnCache)) {
            return $columnCache[$cacheKey];
        }

        [$schemaName, $tableName] = $this->splitResolvedTableName($resolvedTable);
        $params = [$tableName, $columnName];
        $schemaSql = '';
        if ($schemaName !== null) {
            $schemaSql = ' AND table_schema = ?';
            $params[] = $schemaName;
        }

        $row = DB::selectOne(
            "SELECT 1
             FROM information_schema.columns
             WHERE table_name = ?
               AND column_name = ?
               AND table_schema NOT IN ('pg_catalog', 'information_schema')
               {$schemaSql}
             LIMIT 1",
            $params
        );

        $columnCache[$cacheKey] = $row !== null;
        return $columnCache[$cacheKey];
    }

    private function getFirstExistingColumn(string $resolvedTable, array $columnCandidates): ?string
    {
        foreach ($columnCandidates as $columnName) {
            if ($this->tableHasColumn($resolvedTable, $columnName)) {
                return $columnName;
            }
        }

        return null;
    }

    private function getExistingColumns(string $resolvedTable, array $columnCandidates): array
    {
        $columns = [];
        foreach ($columnCandidates as $columnName) {
            if ($this->tableHasColumn($resolvedTable, $columnName)) {
                $columns[] = $columnName;
            }
        }

        return $columns;
    }

    private function splitResolvedTableName(string $resolvedTable): array
    {
        $parts = explode('.', $resolvedTable, 2);
        if (count($parts) === 2) {
            return [$parts[0], $parts[1]];
        }

        return [null, $resolvedTable];
    }

    private function quoteIdentifier(string $identifier): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new \InvalidArgumentException('Identifier database tidak valid.');
        }

        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    private function normalizeDailyRewardVideoUrl(string $url): ?string
    {
        $value = trim($url);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return preg_replace('/^http:\/\/masuk\.bacaanku\.id/i', 'https://masuk.bacaanku.id', $value);
        }

        if (strpos($value, '//') === 0) {
            return 'https:' . $value;
        }

        $path = ltrim($value, '/');
        if ($path === '') {
            return null;
        }

        if (preg_match('/^(masuk\.bacaanku\.id|api\.bacaanku\.id)\//i', $path)) {
            return 'https://' . $path;
        }

        $looksLikeMediaPath = strpos($path, '/') !== false
            || preg_match('/\.(mp4|m4v|mov|webm|m3u8)(\?.*)?$/i', $path);
        if (!$looksLikeMediaPath) {
            return null;
        }

        if (strpos($path, 'storage/') === 0) {
            return 'https://masuk.bacaanku.id/' . $path;
        }

        return 'https://masuk.bacaanku.id/storage/' . $path;
    }

    private function getPackageCountDaily(string $packageName): int
    {
        $normalizedName = strtolower(trim($packageName));
        if (strpos($normalizedName, 'week') !== false || strpos($normalizedName, 'minggu') !== false) {
            return 7;
        }
        if (strpos($normalizedName, 'month') !== false || strpos($normalizedName, 'bulan') !== false) {
            return 30;
        }
        if (strpos($normalizedName, 'year') !== false || strpos($normalizedName, 'tahun') !== false) {
            return 365;
        }

        return 7;
    }

    private function getActiveSubscription(int $userId)
    {
        $now = date('Y-m-d H:i:s');

        DB::table('trx_buy_paket')
            ->where('user_id', $userId)
            ->where('status', true)
            ->where('end_date', '<', $now)
            ->update([
                'status' => false,
                'updated_at' => $now,
            ]);

        return DB::table('trx_buy_paket')
            ->where('user_id', $userId)
            ->where('status', true)
            ->where('status_payment', 'verified')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('end_date', 'desc')
            ->first();
    }

    private function generateApiToken(): string
    {
        return bin2hex(random_bytes(40));
    }
    private function incrementNovelCounter(Request $request, string $type)
    {
        $configs = [
            'read' => [
                'table' => 'trx_read_novel',
                'total_column' => 'total_read',
            ],
            'vote' => [
                'table' => 'trx_vote_novel',
                'total_column' => 'total_vote',
            ],
            'share' => [
                'table' => 'trx_share_novel',
                'total_column' => 'total_share',
            ],
        ];
        if (!isset($configs[$type])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tipe transaksi tidak valid.'
            ], 400);
        }
        $novelId = $request->input('novel_id', $request->input('id'));
        $increment = $request->input('increment', 1);
        if (!filter_var($novelId, FILTER_VALIDATE_INT) || (int)$novelId < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'novel_id wajib diisi dan harus berupa angka lebih dari 0.'
            ], 422);
        }
        if (!filter_var($increment, FILTER_VALIDATE_INT) || (int)$increment < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'increment harus berupa angka lebih dari 0.'
            ], 422);
        }
        $novelId = (int) $novelId;
        $increment = (int) $increment;
        $table = $configs[$type]['table'];
        $totalColumn = $configs[$type]['total_column'];
        $now = date('Y-m-d H:i:s');
        try {
            $result = DB::transaction(function () use ($novelId, $increment, $table, $totalColumn, $now) {
                $totalRows = DB::select(
                    "UPDATE mst_cerita
                     SET {$totalColumn} = (
                         CASE
                             WHEN {$totalColumn}::text ~ '^[0-9]+$' THEN {$totalColumn}::integer
                             ELSE 0
                         END
                     ) + ?, updated_at = ?
                     WHERE id = ?
                     RETURNING {$totalColumn} AS total_count",
                    [$increment, $now, $novelId]
                );
                if (count($totalRows) === 0) {
                    return [
                        'not_found' => true,
                    ];
                }
                $trxRows = DB::select(
                    "INSERT INTO {$table} (novel_id, \"count\", created_at, updated_at)
                     VALUES (?, ?, ?, ?)
                     ON CONFLICT (novel_id) DO UPDATE
                     SET \"count\" = (
                         (
                             CASE
                                 WHEN {$table}.\"count\"::text ~ '^[0-9]+$' THEN {$table}.\"count\"::integer
                                 ELSE 0
                             END
                         ) + EXCLUDED.\"count\"::integer
                     )::varchar,
                     updated_at = EXCLUDED.updated_at
                     RETURNING id, novel_id, \"count\", created_at, updated_at",
                    [$novelId, (string) $increment, $now, $now]
                );
                return [
                    'not_found' => false,
                    'trx' => $trxRows[0],
                    'total_count' => (int) $totalRows[0]->total_count,
                ];
            });
            if ($result['not_found']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data cerita tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Counter berhasil ditambahkan.',
                'type' => $type,
                'novel_id' => $novelId,
                'count' => (int) $result['trx']->count,
                'total_count' => $result['total_count'],
                'results' => $result['trx'],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan counter.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
