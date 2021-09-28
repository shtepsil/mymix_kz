<?php

namespace common\components;

use common\models\HistoryBonus;
use common\models\User;
use common\models\UserInvited;

class ReferralSystem
{
    private $levelPercent = [
        1 => 5,
        2 => 3,
        3 => 2
    ];
    private $max_level    = 3;
    public function hasInvited($user_id)
    {
        return UserInvited::find()->where(['user_invited' => $user_id, 'status' => 1])->exists();
    }
    public function parentUsersPercent($user_id)
    {
        $parent = UserInvited::find()->where(['user_invited' => $user_id, 'status' => 1])->one();
        $result = [];
        $level  = 1;
        if ($parent) {
            $result[$level] = [
                'user_id' => $parent->user_id,
                'percent' => $this->levelPercent[$level]
            ];
            $current_id     = $parent->user_id;
            $search         = true;
            while ($search) {
                $level++;
                $next_parent = UserInvited::find()->where(['user_invited' => $current_id, 'status' => 1])->one();
                if ($next_parent) {
                    $current_id     = $next_parent->user_id;
                    $result[$level] = [
                        'user_id' => $next_parent->user_id,
                        'percent' => $this->levelPercent[$level]
                    ];
                }
                if ($this->max_level == $level || !$next_parent) {
                    $search = false;
                }
            }
        }
        return $result;
    }
    public function addBonus($sum, $user_id)
    {
        $parents = $this->parentUsersPercent($user_id);
        foreach ($parents as $parent) {
            $full_bonus          = floor(((int)$sum * ($parent['percent'])) / 100);
            $history             = new HistoryBonus();
            $history->user_id    = $parent['user_id'];
            $history->created_at = time();
            $history->name       = 'Партнёрская программа';
            $history->sum        = $full_bonus;
            $history->save(false);
            User::updateAllCounters(['bonus' => $full_bonus], ['id' => $parent['user_id']]);
        }
    }
    public function addInvited($user_id, $user_invited, $order_id)
    {
        $record               = new UserInvited();
        $record->user_id      = $user_id;
        $record->user_invited = $user_invited;
        $record->status       = 0;
        $record->order_id     = $order_id;
        $record->save();
    }
    public function successInvited($order_id)
    {
        $invited = UserInvited::find()->where(['order_id' => $order_id])->one();
        if ($invited && $invited->status != 1) {
            $invited->status = 1;
            $invited->save();
            UserInvited::deleteAll([
                'and',
                ['<>', 'id', $invited->id],
                ['user_invited' => $invited->user_invited]
            ]);
        }
    }

}