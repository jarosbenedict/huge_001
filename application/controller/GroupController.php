<?php

class GroupController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::checkAuthentication();
    }

    /**
     * List all groups the current user belongs to.
     */
    public function index()
    {
        $this->View->render('group/index', array(
            'groups' => GroupModel::getGroupsForUser()
        ));
    }

    /**
     * GET  → show create-group form
     * POST → process form, redirect to new group's conversation
     */
    public function create()
    {
        if (Request::post('submit_create_group')) {
            $group_name  = Request::post('group_name');
            $member_ids  = Request::post('member_ids'); // array of user_ids

            $group_id = GroupModel::createGroup($group_name);

            if ($group_id) {
                // add any additionally selected members
                if (is_array($member_ids)) {
                    foreach ($member_ids as $uid) {
                        GroupModel::addMember($group_id, (int) $uid);
                    }
                }
                Session::add('feedback_positive', 'Group created successfully.');
                Redirect::to('group/conversation/' . $group_id);
                return;
            }
        }

        // fetch all users for the member-selection list (excluding self)
        $me       = (int) Session::get('user_id');
        $database = DatabaseFactory::getFactory()->getConnection();
        $sql      = "CALL sp_get_all_users_except(:me)";
        $query    = $database->prepare($sql);
        $query->execute(array(':me' => $me));
        $all_users = $query->fetchAll();

        $this->View->render('group/create', array(
            'all_users' => $all_users
        ));
    }

    /**
     * Show the group chat. Only members may view it.
     */
    public function conversation($group_id)
    {
        $group_id = (int) $group_id;
        $me       = (int) Session::get('user_id');

        $group = GroupModel::getGroupById($group_id);
        if (!$group || !GroupModel::isMember($group_id, $me)) {
            Session::add('feedback_negative', 'Group not found or access denied.');
            Redirect::to('group');
            return;
        }

        GroupModel::markGroupRead($group_id);

        $this->View->render('group/conversation', array(
            'group'      => $group,
            'messages'   => GroupModel::getGroupMessages($group_id),
            'members'    => GroupModel::getMembers($group_id),
            'non_members'=> GroupModel::getNonMembers($group_id),
            'is_creator' => GroupModel::isCreator($group_id, $me),
            'group_id'   => $group_id
        ));
    }

    /**
     * Handle message POST for a group, then redirect back to conversation.
     */
    public function send($group_id)
    {
        $group_id = (int) $group_id;
        $me       = (int) Session::get('user_id');

        if (!GroupModel::isMember($group_id, $me)) {
            Session::add('feedback_negative', 'Access denied.');
            Redirect::to('group');
            return;
        }

        $text = Request::post('message_text');
        GroupModel::sendGroupMessage($group_id, $text);

        Redirect::to('group/conversation/' . $group_id);
    }

    /**
     * Add a member to a group (creator only, POST).
     */
    public function addMember($group_id)
    {
        $group_id = (int) $group_id;
        $me       = (int) Session::get('user_id');

        if (!GroupModel::isCreator($group_id, $me)) {
            Session::add('feedback_negative', 'Only the group creator can add members.');
            Redirect::to('group/conversation/' . $group_id);
            return;
        }

        $user_id = (int) Request::post('user_id');
        if ($user_id > 0) {
            GroupModel::addMember($group_id, $user_id);
        }

        Redirect::to('group/conversation/' . $group_id);
    }

    /**
     * Remove a member from a group (creator only, or self-leave).
     */
    public function removeMember($group_id, $user_id)
    {
        $group_id = (int) $group_id;
        $user_id  = (int) $user_id;
        $me       = (int) Session::get('user_id');

        if ($user_id !== $me && !GroupModel::isCreator($group_id, $me)) {
            Session::add('feedback_negative', 'Access denied.');
        } else {
            GroupModel::removeMember($group_id, $user_id);
        }

        Redirect::to('group/conversation/' . $group_id);
    }
}
