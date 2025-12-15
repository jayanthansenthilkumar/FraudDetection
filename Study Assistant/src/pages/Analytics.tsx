import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { supabase } from "@/integrations/supabase/client";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Loader2, MessageSquare, FileText, BookOpen, TrendingUp, Calendar } from "lucide-react";
import { Session } from "@supabase/supabase-js";

const Analytics = () => {
  const [session, setSession] = useState<Session | null>(null);
  const [loading, setLoading] = useState(true);
  const [stats, setStats] = useState({
    totalChats: 0,
    totalMessages: 0,
    totalNotes: 0,
    totalMaterials: 0,
    thisWeekChats: 0,
    thisMonthChats: 0,
  });
  const navigate = useNavigate();

  useEffect(() => {
    supabase.auth.getSession().then(({ data: { session } }) => {
      setSession(session);
      setLoading(false);
      if (!session) {
        navigate("/auth");
      } else {
        fetchAnalytics();
      }
    });

    const { data: { subscription } } = supabase.auth.onAuthStateChange((_event, session) => {
      setSession(session);
      if (!session) {
        navigate("/auth");
      }
    });

    return () => subscription.unsubscribe();
  }, [navigate]);

  const fetchAnalytics = async () => {
    try {
      // Total chats
      const { count: totalChats } = await supabase
        .from("chat_sessions")
        .select("*", { count: "exact", head: true });

      // Total messages
      const { count: totalMessages } = await supabase
        .from("chat_messages")
        .select("*", { count: "exact", head: true });

      // Total notes
      const { count: totalNotes } = await supabase
        .from("notes")
        .select("*", { count: "exact", head: true });

      // Total materials
      const { count: totalMaterials } = await supabase
        .from("study_materials")
        .select("*", { count: "exact", head: true });

      // This week's chats
      const weekAgo = new Date();
      weekAgo.setDate(weekAgo.getDate() - 7);
      const { count: thisWeekChats } = await supabase
        .from("chat_sessions")
        .select("*", { count: "exact", head: true })
        .gte("created_at", weekAgo.toISOString());

      // This month's chats
      const monthAgo = new Date();
      monthAgo.setMonth(monthAgo.getMonth() - 1);
      const { count: thisMonthChats } = await supabase
        .from("chat_sessions")
        .select("*", { count: "exact", head: true })
        .gte("created_at", monthAgo.toISOString());

      setStats({
        totalChats: totalChats || 0,
        totalMessages: totalMessages || 0,
        totalNotes: totalNotes || 0,
        totalMaterials: totalMaterials || 0,
        thisWeekChats: thisWeekChats || 0,
        thisMonthChats: thisMonthChats || 0,
      });
    } catch (error) {
      console.error("Error fetching analytics:", error);
    }
  };

  if (loading) {
    return (
      <div className="flex-1 flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="flex-1 overflow-auto">
      <div className="container mx-auto px-4 py-8">
        <div className="max-w-6xl mx-auto space-y-8">
          {/* Header */}
          <div className="space-y-2">
            <h2 className="text-3xl font-bold text-foreground flex items-center gap-2">
              <TrendingUp className="h-8 w-8" />
              Learning Analytics
            </h2>
            <p className="text-muted-foreground text-lg">
              Track your learning progress and insights
            </p>
          </div>

          {/* Overview Stats */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <MessageSquare className="h-4 w-4" />
                  Total Conversations
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold">{stats.totalChats}</div>
                <p className="text-xs text-muted-foreground mt-1">
                  {stats.totalMessages} messages
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <FileText className="h-4 w-4" />
                  Notes Created
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold">{stats.totalNotes}</div>
                <p className="text-xs text-muted-foreground mt-1">All time</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <BookOpen className="h-4 w-4" />
                  Study Materials
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold">{stats.totalMaterials}</div>
                <p className="text-xs text-muted-foreground mt-1">Resources saved</p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="pb-3">
                <CardTitle className="text-sm font-medium text-muted-foreground flex items-center gap-2">
                  <Calendar className="h-4 w-4" />
                  This Week
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-3xl font-bold">{stats.thisWeekChats}</div>
                <p className="text-xs text-muted-foreground mt-1">Sessions started</p>
              </CardContent>
            </Card>
          </div>

          {/* Activity Overview */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Activity Trends</CardTitle>
                <CardDescription>Your learning activity over time</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <div className="flex justify-between text-sm mb-2">
                    <span className="text-muted-foreground">This Week</span>
                    <span className="font-medium">{stats.thisWeekChats} sessions</span>
                  </div>
                  <div className="h-2 bg-secondary rounded-full overflow-hidden">
                    <div 
                      className="h-full bg-blue-500 transition-all"
                      style={{ width: `${Math.min((stats.thisWeekChats / stats.totalChats) * 100, 100)}%` }}
                    />
                  </div>
                </div>

                <div>
                  <div className="flex justify-between text-sm mb-2">
                    <span className="text-muted-foreground">This Month</span>
                    <span className="font-medium">{stats.thisMonthChats} sessions</span>
                  </div>
                  <div className="h-2 bg-secondary rounded-full overflow-hidden">
                    <div 
                      className="h-full bg-green-500 transition-all"
                      style={{ width: `${Math.min((stats.thisMonthChats / stats.totalChats) * 100, 100)}%` }}
                    />
                  </div>
                </div>

                <div>
                  <div className="flex justify-between text-sm mb-2">
                    <span className="text-muted-foreground">All Time</span>
                    <span className="font-medium">{stats.totalChats} sessions</span>
                  </div>
                  <div className="h-2 bg-secondary rounded-full overflow-hidden">
                    <div className="h-full bg-primary transition-all" style={{ width: "100%" }} />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Content Summary</CardTitle>
                <CardDescription>Overview of your created content</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-6">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <div className="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <MessageSquare className="h-5 w-5 text-primary" />
                      </div>
                      <div>
                        <p className="font-medium">Conversations</p>
                        <p className="text-sm text-muted-foreground">
                          Avg {stats.totalChats > 0 ? Math.round(stats.totalMessages / stats.totalChats) : 0} messages/chat
                        </p>
                      </div>
                    </div>
                    <div className="text-2xl font-bold">{stats.totalChats}</div>
                  </div>

                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <div className="h-10 w-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                        <FileText className="h-5 w-5 text-green-600" />
                      </div>
                      <div>
                        <p className="font-medium">Notes</p>
                        <p className="text-sm text-muted-foreground">Personal notes</p>
                      </div>
                    </div>
                    <div className="text-2xl font-bold">{stats.totalNotes}</div>
                  </div>

                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      <div className="h-10 w-10 rounded-lg bg-orange-500/10 flex items-center justify-center">
                        <BookOpen className="h-5 w-5 text-orange-600" />
                      </div>
                      <div>
                        <p className="font-medium">Study Materials</p>
                        <p className="text-sm text-muted-foreground">Saved resources</p>
                      </div>
                    </div>
                    <div className="text-2xl font-bold">{stats.totalMaterials}</div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Insights */}
          <Card>
            <CardHeader>
              <CardTitle>Learning Insights</CardTitle>
              <CardDescription>AI-powered suggestions based on your activity</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="p-4 rounded-lg bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900">
                  <h4 className="font-medium mb-1">Great Progress!</h4>
                  <p className="text-sm text-muted-foreground">
                    You've had {stats.thisWeekChats} learning sessions this week. Keep up the momentum!
                  </p>
                </div>
                
                {stats.totalNotes === 0 && (
                  <div className="p-4 rounded-lg bg-orange-50 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-900">
                    <h4 className="font-medium mb-1">Try Taking Notes</h4>
                    <p className="text-sm text-muted-foreground">
                      Taking notes can help reinforce your learning. Start creating notes from your conversations!
                    </p>
                  </div>
                )}

                {stats.totalMaterials === 0 && (
                  <div className="p-4 rounded-lg bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-900">
                    <h4 className="font-medium mb-1">Add Study Materials</h4>
                    <p className="text-sm text-muted-foreground">
                      Organize your learning resources by adding study materials and references.
                    </p>
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default Analytics;
